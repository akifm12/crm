<?php
// app/Http/Controllers/Admin/SettingsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaTemplate;
use App\Models\QuotationTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $slaTemplates = SlaTemplate::latest()->get();
        $qtTemplates  = QuotationTemplate::latest()->get();
        $staff        = User::orderBy('name')->get();
        return view('admin.settings.index', compact('slaTemplates', 'qtTemplates', 'staff'));
    }

    // ── SLA Templates ──────────────────────────────────────────────────────

    public function createSlaTemplate()
    {
        return view('admin.settings.sla-template-form', ['template' => null]);
    }

    public function storeSlaTemplate(Request $request)
    {
        $request->validate(['name' => 'required', 'service_type' => 'required', 'scope_of_work' => 'required']);
        SlaTemplate::create(array_merge($request->all(), ['created_by' => auth()->id()]));
        return redirect()->route('settings.index', ['#sla-templates'])
            ->with('success', 'SLA template created.');
    }

    public function editSlaTemplate(SlaTemplate $template)
    {
        return view('admin.settings.sla-template-form', compact('template'));
    }

    public function updateSlaTemplate(Request $request, SlaTemplate $template)
    {
        $request->validate(['name' => 'required', 'service_type' => 'required', 'scope_of_work' => 'required']);
        $template->update($request->all());
        return redirect()->route('settings.index')
            ->with('success', 'SLA template updated.');
    }

    public function toggleSlaTemplate(SlaTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);
        return back()->with('success', 'Template ' . ($template->is_active ? 'activated' : 'deactivated') . '.');
    }

    // ── Quotation Templates ────────────────────────────────────────────────

    public function createQtTemplate()
    {
        return view('admin.settings.qt-template-form', ['template' => null]);
    }

    public function storeQtTemplate(Request $request)
    {
        $request->validate(['name' => 'required', 'service_type' => 'required']);
        $items = collect($request->input('items', []))
            ->filter(fn($i) => !empty($i['description']))
            ->values()
            ->toArray();
        QuotationTemplate::create([
            'name'          => $request->name,
            'service_type'  => $request->service_type,
            'description'   => $request->description,
            'line_items'    => $items,
            'terms'         => $request->terms,
            'validity_days' => $request->validity_days ?? 30,
            'is_active'     => true,
            'created_by'    => auth()->id(),
        ]);
        return redirect()->route('settings.index')->with('success', 'Quotation template created.');
    }

    public function editQtTemplate(QuotationTemplate $template)
    {
        return view('admin.settings.qt-template-form', compact('template'));
    }

    public function updateQtTemplate(Request $request, QuotationTemplate $template)
    {
        $request->validate(['name' => 'required', 'service_type' => 'required']);
        $items = collect($request->input('items', []))
            ->filter(fn($i) => !empty($i['description']))
            ->values()
            ->toArray();
        $template->update([
            'name'          => $request->name,
            'service_type'  => $request->service_type,
            'description'   => $request->description,
            'line_items'    => $items,
            'terms'         => $request->terms,
            'validity_days' => $request->validity_days ?? 30,
        ]);
        return redirect()->route('settings.index')->with('success', 'Quotation template updated.');
    }

    public function toggleQtTemplate(QuotationTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);
        return back()->with('success', 'Template ' . ($template->is_active ? 'activated' : 'deactivated') . '.');
    }

    // ── Staff users ────────────────────────────────────────────────────────

    public function storeStaff(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => 'required|in:admin,staff',
        ]);
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);
        return back()->with('success', 'Staff user created.');
    }

    public function toggleStaff(User $user)
    {
        // We use a soft approach — just update role to 'inactive' or back
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }
        $user->update(['role' => $user->role === 'staff' ? 'inactive' : 'staff']);
        return back()->with('success', 'User updated.');
    }
}
