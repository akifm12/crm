<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmClient;
use App\Models\CrmEmployeeTraining;
use App\Mail\CertificateLinksEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TrainingSessionController extends Controller
{
    public function index()
    {
        $sessions = CrmEmployeeTraining::select(
                'crm_employee_trainings.training_type',
                'crm_employee_trainings.training_date',
                DB::raw('COUNT(*) as attendee_count'),
                DB::raw('MAX(crm_employee_trainings.trainer) as trainer'),
                DB::raw('MAX(crm_employee_trainings.certificate_template) as certificate_template'),
                DB::raw('GROUP_CONCAT(DISTINCT crm_clients.company_name ORDER BY crm_clients.company_name SEPARATOR ", ") as client_names')
            )
            ->join('crm_clients', 'crm_employee_trainings.crm_client_id', '=', 'crm_clients.id')
            ->groupBy('crm_employee_trainings.training_type', 'crm_employee_trainings.training_date')
            ->orderByDesc('crm_employee_trainings.training_date')
            ->get();

        return view('admin.training.sessions', compact('sessions'));
    }

    public function show(Request $request, string $date, string $type)
    {
        $attendees = CrmEmployeeTraining::with('client')
            ->where('training_type', $type)
            ->whereDate('training_date', $date)
            ->orderBy('employee_name')
            ->get();

        $clients = CrmClient::orderBy('company_name')->get(['id', 'company_name']);

        return view('admin.training.session_show', compact('attendees', 'date', 'type', 'clients'));
    }

    public function addAttendee(Request $request, string $date, string $type)
    {
        $validated = $request->validate([
            'employee_name'        => 'required|string|max:255',
            'employee_role'        => 'nullable|string|max:255',
            'employee_id_number'   => 'nullable|string|max:100',
            'crm_client_id'        => 'required|exists:crm_clients,id',
            'expiry_date'          => 'nullable|date',
            'status'               => 'required|in:completed,pending',
            'signatory_name'       => 'nullable|string|max:255',
            'signatory_title'      => 'nullable|string|max:255',
            'certificate_template' => 'nullable|integer|min:1|max:3',
        ]);

        CrmEmployeeTraining::create(array_merge($validated, [
            'training_type'     => $type,
            'training_date'     => $date,
        ]));

        return redirect()->route('training-sessions.show', ['date' => $date, 'type' => $type])
            ->with('success', 'Attendee added.');
    }

    public function create()
    {
        $clients = CrmClient::orderBy('company_name')->get(['id', 'company_name']);
        return view('admin.training.session_create', compact('clients'));
    }

    public function emailClients(Request $request, string $date, string $type)
    {
        $attendees = CrmEmployeeTraining::with('client')
            ->where('training_type', $type)
            ->whereDate('training_date', $date)
            ->whereNotNull('public_token')
            ->get();

        $byClient = $attendees->groupBy('crm_client_id');
        $sent     = 0;
        $skipped  = 0;
        $sessionDate = \Carbon\Carbon::parse($date)->format('d F Y');

        foreach ($byClient as $clientId => $records) {
            $client = $records->first()->client;

            if (empty($client->email)) {
                $skipped++;
                continue;
            }

            Mail::to($client->email)->cc('contact@bluearrow.ae')->send(new CertificateLinksEmail(
                companyName:  $client->company_name,
                sessionTitle: $type,
                sessionDate:  $sessionDate,
                attendees:    $records,
            ));

            $sent++;
        }

        $message = "Emails sent to {$sent} " . ($sent === 1 ? 'company' : 'companies') . '.';
        if ($skipped) $message .= " {$skipped} skipped (no email on file).";

        return back()->with('success', $message);
    }

    // ── Training log data + generator (shared between Word and PDF) — reproduces
    //    the real BAMC letterhead (logo/address in the header, licensing line in
    //    the footer, from "BAMC empty Letterhead with WM.docx") via generate-
    //    training-log.cjs, instead of a blank page the user pastes it onto. ────

    private function buildTrainingLogData($attendees, string $date, string $type): array
    {
        $logoPath = resource_path('branding/bamc-logo.jpeg');

        return [
            'training_type'  => $type,
            'date_formatted' => \Carbon\Carbon::parse($date)->format('d F Y'),
            'trainer'        => $attendees->first()->trainer ?? '',
            'logo_path'      => file_exists($logoPath) ? $logoPath : null,
            'attendees'      => $attendees->map(fn ($a) => [
                'employee_name'      => $a->employee_name,
                'employee_id_number' => $a->employee_id_number,
                'company_name'       => $a->client->company_name ?? null,
            ])->values()->all(),
        ];
    }

    private function generateTrainingLogDocx(array $data, string $baseName): string
    {
        $tmpJson = storage_path('app/tmp/training_' . uniqid() . '.json');
        $outPath = storage_path('app/tmp/' . $baseName . '.docx');

        if (!file_exists(dirname($tmpJson))) mkdir(dirname($tmpJson), 0755, true);

        file_put_contents($tmpJson, json_encode($data, JSON_UNESCAPED_UNICODE));

        $cmd    = 'node ' . escapeshellarg(base_path('scripts/generate-training-log.cjs')) . ' ' . escapeshellarg($tmpJson) . ' ' . escapeshellarg($outPath) . ' 2>&1';
        $output = shell_exec($cmd);
        @unlink($tmpJson);

        if (!file_exists($outPath)) {
            throw new \RuntimeException('Training log docx generation failed: ' . $output);
        }

        return $outPath;
    }

    public function exportLogDocx(string $date, string $type)
    {
        $attendees = CrmEmployeeTraining::with('client')
            ->where('training_type', $type)
            ->whereDate('training_date', $date)
            ->orderBy('employee_name')
            ->get();

        $baseName = 'Training-Log-' . Str::slug($type) . '-' . $date . '-' . uniqid();
        $filename = 'Training-Log-' . Str::slug($type) . '-' . $date . '.docx';

        try {
            $outPath = $this->generateTrainingLogDocx($this->buildTrainingLogData($attendees, $date, $type), $baseName);
        } catch (\RuntimeException $e) {
            Log::error('Training log docx failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate the Word document. ' . $e->getMessage());
        }

        return response()->download($outPath, $filename)->deleteFileAfterSend(true);
    }

    // ── Training log PDF (converted from the same letterhead docx via LibreOffice,
    //    mirroring ReportController::kycPdf's docx-to-pdf pattern) ────────────
    public function exportLog(string $date, string $type)
    {
        $attendees = CrmEmployeeTraining::with('client')
            ->where('training_type', $type)
            ->whereDate('training_date', $date)
            ->orderBy('employee_name')
            ->get();

        $baseName = 'Training-Log-' . Str::slug($type) . '-' . $date . '-' . uniqid();
        $filename = 'Training-Log-' . Str::slug($type) . '-' . $date . '.pdf';

        try {
            $docxPath = $this->generateTrainingLogDocx($this->buildTrainingLogData($attendees, $date, $type), $baseName);
        } catch (\RuntimeException $e) {
            Log::error('Training log PDF: docx step failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to generate the training log. ' . $e->getMessage());
        }

        $tmpDir  = storage_path('app/tmp');
        $soffice = env('SOFFICE_PATH', 'soffice');
        $cmd     = 'HOME=/tmp ' . escapeshellarg($soffice) . ' --headless --convert-to pdf --outdir ' . escapeshellarg($tmpDir) . ' ' . escapeshellarg($docxPath) . ' 2>&1';
        $output  = shell_exec($cmd);
        @unlink($docxPath);

        $pdfPath = $tmpDir . '/' . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';

        if (!file_exists($pdfPath)) {
            Log::error('Training log PDF: soffice conversion failed', ['output' => $output]);
            return back()->with('error', 'PDF conversion failed. Ensure LibreOffice is installed on the server.');
        }

        return response()->download($pdfPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }

    // ── Bulk attendee import from an uploaded Excel/CSV file ──────────────────

    public function importTemplate()
    {
        $csv = "Name,ID Number,Role\nJohn Doe,784-1990-1234567-1,Compliance Officer\nJane Smith,784-1991-7654321-2,\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendee-import-template.csv"',
        ]);
    }

    public function importAttendees(Request $request, string $date, string $type)
    {
        $request->validate([
            'crm_client_id'         => 'required|exists:crm_clients,id',
            'status'                => 'required|in:completed,pending',
            'expiry_date'           => 'nullable|date',
            'signatory_name'        => 'nullable|string|max:255',
            'signatory_title'       => 'nullable|string|max:255',
            'certificate_template'  => 'nullable|integer|min:1|max:3',
            'file'                  => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $rows = $this->parseAttendeeSpreadsheet($request->file('file'));
        } catch (\Throwable $e) {
            Log::error('Attendee import: failed to parse file', ['error' => $e->getMessage()]);
            return back()->with('error', 'Could not read that file. Make sure it is a valid Excel (.xlsx/.xls) or CSV file.');
        }

        if (empty($rows)) {
            return back()->with('error', 'No attendee names were found in the uploaded file.');
        }

        foreach ($rows as $row) {
            CrmEmployeeTraining::create([
                'employee_name'        => $row['name'],
                'employee_id_number'   => $row['id_number'],
                'employee_role'        => $row['role'],
                'crm_client_id'        => $request->crm_client_id,
                'expiry_date'          => $request->expiry_date,
                'status'               => $request->status,
                'training_type'        => $type,
                'training_date'        => $date,
                'signatory_name'       => $request->signatory_name,
                'signatory_title'      => $request->signatory_title,
                'certificate_template' => $request->certificate_template ?? 1,
            ]);
        }

        return redirect()->route('training-sessions.show', ['date' => $date, 'type' => $type])
            ->with('success', count($rows) . ' attendee(s) imported from the file.');
    }

    // Reads Name / ID Number / Role from an uploaded spreadsheet. Recognizes a
    // flexible set of header aliases (case/spacing-insensitive); if the first
    // row does not look like a header at all, falls back to treating every row
    // as data in the fixed order Name, ID Number, Role.
    private function parseAttendeeSpreadsheet($uploadedFile): array
    {
        $spreadsheet = IOFactory::load($uploadedFile->getRealPath());
        $data        = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (empty($data)) return [];

        $aliases = [
            'name'      => ['name', 'employee name', 'full name', 'trainee name', 'attendee name'],
            'id_number' => ['id number', 'id no', 'idno', 'emirates id', 'eid', 'employee id', 'id'],
            'role'      => ['role', 'position', 'designation', 'job title'],
        ];
        $normalize = fn ($v) => strtolower(trim(preg_replace('/\s+/', ' ', (string) $v)));

        $headerRow = array_map($normalize, $data[0]);
        $colMap    = [];
        foreach ($aliases as $field => $names) {
            foreach ($headerRow as $i => $h) {
                if (in_array($h, $names, true)) { $colMap[$field] = $i; break; }
            }
        }

        if (isset($colMap['name'])) {
            $startRow = 1;
        } else {
            $colMap   = ['name' => 0, 'id_number' => 1, 'role' => 2];
            $startRow = 0;
        }

        $rows = [];
        for ($r = $startRow; $r < count($data); $r++) {
            $row  = $data[$r];
            $name = trim((string) ($row[$colMap['name']] ?? ''));
            if ($name === '') continue;

            $idNumber = isset($colMap['id_number']) ? trim((string) ($row[$colMap['id_number']] ?? '')) : '';
            $role     = isset($colMap['role'])      ? trim((string) ($row[$colMap['role']] ?? ''))      : '';

            $rows[] = [
                'name'      => $name,
                'id_number' => $idNumber !== '' ? $idNumber : null,
                'role'      => $role !== '' ? $role : null,
            ];
        }

        return $rows;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'training_type'        => 'required|string|max:255',
            'training_date'        => 'required|date',
            'trainer'              => 'nullable|string|max:255',
            'certificate_template' => 'nullable|integer|min:1|max:3',
            'signatory_name'       => 'nullable|string|max:255',
            'signatory_title'      => 'nullable|string|max:255',
            'attendees'            => 'required|array|min:1',
            'attendees.*.employee_name'      => 'required|string|max:255',
            'attendees.*.employee_role'      => 'nullable|string|max:255',
            'attendees.*.employee_id_number' => 'nullable|string|max:100',
            'attendees.*.crm_client_id'      => 'required|exists:crm_clients,id',
            'attendees.*.expiry_date'   => 'nullable|date',
            'attendees.*.status'        => 'required|in:completed,pending',
        ]);

        foreach ($validated['attendees'] as $attendee) {
            CrmEmployeeTraining::create(array_merge($attendee, [
                'training_type'        => $validated['training_type'],
                'training_date'        => $validated['training_date'],
                'trainer'              => $validated['trainer'] ?? null,
                'certificate_template' => $validated['certificate_template'] ?? 1,
                'signatory_name'       => $validated['signatory_name'] ?? null,
                'signatory_title'      => $validated['signatory_title'] ?? null,
            ]));
        }

        $date = $validated['training_date'];
        $type = $validated['training_type'];

        return redirect()->route('training-sessions.show', ['date' => $date, 'type' => $type])
            ->with('success', 'Training session created with ' . count($validated['attendees']) . ' attendee(s).');
    }
}
