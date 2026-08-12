<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmClient;
use App\Models\CrmEmployeeTraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainingSessionController extends Controller
{
    public function index()
    {
        $sessions = CrmEmployeeTraining::select(
                'training_type',
                'training_date',
                DB::raw('COUNT(*) as attendee_count'),
                DB::raw('MAX(trainer) as trainer'),
                DB::raw('MAX(certificate_template) as certificate_template')
            )
            ->groupBy('training_type', 'training_date')
            ->orderByDesc('training_date')
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
            'employee_name'      => 'required|string|max:255',
            'employee_role'      => 'nullable|string|max:255',
            'crm_client_id'      => 'required|exists:crm_clients,id',
            'expiry_date'        => 'nullable|date',
            'status'             => 'required|in:completed,pending',
            'signatory_name'     => 'nullable|string|max:255',
            'signatory_title'    => 'nullable|string|max:255',
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
            'attendees.*.employee_name' => 'required|string|max:255',
            'attendees.*.employee_role' => 'nullable|string|max:255',
            'attendees.*.crm_client_id' => 'required|exists:crm_clients,id',
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
