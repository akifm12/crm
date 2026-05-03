<?php
// app/Http/Controllers/Admin/TenantController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Support\SectorConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withCount('clients')->latest()->get();
        $sectors = SectorConfig::sectors();
        return view('admin.tenants.index', compact('tenants', 'sectors'));
    }

    public function create()
    {
        $sectors = SectorConfig::sectors();
        return view('admin.tenants.create', compact('sectors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => 'required|string|max:50|unique:tenants,slug|alpha_dash',
            'business_type' => 'required|in:' . implode(',', array_keys(SectorConfig::sectors())),
            'contact_email' => 'required|email',
            'admin_name'    => 'required|string|max:255',
            'admin_email'   => 'required|email|unique:users,email',
            'admin_password'=> 'required|string|min:8',
        ]);

        // Create tenant
        $tenant = Tenant::create([
            'name'          => $request->name,
            'slug'          => $request->slug,
            'business_type' => $request->business_type,
            'contact_email' => $request->contact_email,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'dnfbp_reg_no'  => $request->dnfbp_reg_no,
            'is_active'     => true,
        ]);

        // Create portal login user (uses standard users table)
        User::create([
            'name'     => $request->admin_name,
            'email'    => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'role'     => 'admin',
        ]);

        return redirect()->route('kyc.tenants')
            ->with('success', "Portal created: {$tenant->portalUrl()} — Login: {$request->admin_email}");
    }

    public function edit(Tenant $tenant)
    {
        $sectors = SectorConfig::sectors();
        return view('admin.tenants.edit', compact('tenant', 'sectors'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'business_type' => 'required|in:' . implode(',', array_keys(SectorConfig::sectors())),
            'contact_email' => 'required|email',
        ]);

        $tenant->update($request->only([
            'name', 'business_type', 'contact_email',
            'phone', 'address', 'dnfbp_reg_no', 'is_active',
        ]));

        return back()->with('success', 'Tenant updated.');
    }

    public function toggle(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);
        return back()->with('success', 'Tenant ' . ($tenant->is_active ? 'activated' : 'deactivated') . '.');
    }
}
