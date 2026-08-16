<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmClient;
use App\Models\CrmEmployeeTraining;
use App\Mail\CertificateLinksEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;

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

    public function exportLog(string $date, string $type)
    {
        $attendees = CrmEmployeeTraining::with('client')
            ->where('training_type', $type)
            ->whereDate('training_date', $date)
            ->orderBy('employee_name')
            ->get();

        $letterheadPath = storage_path('app/public/certificate/letterhead.png');
        $letterheadB64  = file_exists($letterheadPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($letterheadPath))
            : null;

        $html = view('admin.training.log_pdf', compact('attendees', 'date', 'type', 'letterheadB64'))->render();

        $filename = 'Training-Log-' . $date . '-' . Str::slug($type) . '.pdf';

        putenv('HOME=/tmp');

        $pdf = Browsershot::html($html)
            ->setChromePath('/usr/bin/google-chrome-stable')
            ->setNodeModulePath('/usr/lib/node_modules')
            ->addChromiumArguments(['disable-dev-shm-usage', 'disable-gpu', 'no-zygote'])
            ->format('A4')
            ->noSandbox()
            ->showBackground()
            ->pdf();

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
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
