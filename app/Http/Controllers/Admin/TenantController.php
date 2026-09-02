<?php
// app/Http/Controllers/Admin/TenantController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmClient;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Accounting\ChartOfAccountSeeder;
use App\Services\Accounting\OtcChartOfAccountSeeder;
use App\Support\SectorConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        $clients = CrmClient::whereDoesntHave('tenantPortal')
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'email', 'telephone', 'address']);
        return view('admin.tenants.create', compact('sectors', 'clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'crm_client_id' => 'required|exists:crm_clients,id',
            'slug'          => ['required', 'string', 'max:50', 'unique:tenants,slug', 'alpha_dash', Rule::notIn(Tenant::reservedSlugs())],
            'business_type' => 'required|in:' . implode(',', array_keys(SectorConfig::sectors())),
            'admin_name'    => 'required|string|max:255',
            'admin_email'   => 'required|email|unique:users,email',
            'admin_password'=> 'required|string|min:8',
        ], [
            'slug.not_in' => 'That acronym is reserved by the site (a page or system route already uses it) and can\'t be used as a tenant portal address.',
        ]);

        $crmClient = CrmClient::findOrFail($request->crm_client_id);

        // Create tenant
        $tenant = Tenant::create([
            'crm_client_id' => $crmClient->id,
            'name'          => $crmClient->company_name,
            'slug'          => $request->slug,
            'business_type' => $request->business_type,
            'contact_email' => $crmClient->email ?? $request->admin_email,
            'phone'         => $crmClient->telephone,
            'address'       => $crmClient->address,
            'dnfbp_reg_no'  => $request->dnfbp_reg_no,
            'is_active'     => true,
        ]);

        // Create portal login user
        User::create([
            'name'      => $request->admin_name,
            'email'     => $request->admin_email,
            'password'  => Hash::make($request->admin_password),
            'role'      => 'admin',
            'tenant_id' => $tenant->id,
        ]);

        return redirect()->route('kyc.tenants')
            ->with('success', "Portal created: {$tenant->portalUrl()} — Login: {$request->admin_email}");
    }

    public function edit(Tenant $tenant)
    {
        $sectors = SectorConfig::sectors();
        $users   = User::where('tenant_id', $tenant->id)->get();
        return view('admin.tenants.edit', compact('tenant', 'sectors', 'users'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'business_type' => 'required|in:' . implode(',', array_keys(SectorConfig::sectors())),
            'contact_email' => 'required|email',
        ]);

        $settings = $tenant->settings ?? [];

        $modules = ['bullion_accounting', 'general_accounting', 'real_estate_accounting'];
        $wasEnabled = [];
        foreach ($modules as $mod) {
            $wasEnabled[$mod] = $tenant->hasModule($mod);
            $settings['enabled_modules'][$mod] = $request->boolean("module_{$mod}");
        }

        // B-Book is a bullion-specific extension — never enable it without Accounting.
        $bBookWasEnabled = $tenant->hasModule('b_book');
        $settings['enabled_modules']['b_book'] = $settings['enabled_modules']['bullion_accounting']
            && $request->boolean('module_b_book');

        $tenant->update($request->only([
            'name', 'business_type', 'contact_email',
            'phone', 'address', 'dnfbp_reg_no', 'vat_trn', 'is_active',
        ]) + ['settings' => $settings]);

        $fresh = $tenant->fresh();
        foreach ($modules as $mod) {
            if (! $wasEnabled[$mod] && $request->boolean("module_{$mod}")) {
                app(ChartOfAccountSeeder::class)->seedForTenant($fresh, ChartOfAccountSeeder::templateForModule($mod));
            }
        }
        if (! $bBookWasEnabled && $fresh->hasModule('b_book')) {
            app(OtcChartOfAccountSeeder::class)->seedForTenant($fresh);
        }

        return back()->with('success', 'Tenant updated.');
    }

    public function addUser(Request $request, Tenant $tenant)
    {
        $request->validate([
            'user_name'     => 'required|string|max:255',
            'user_email'    => 'required|email|unique:users,email',
            'user_password' => 'required|string|min:6',
        ]);

        User::create([
            'name'          => $request->user_name,
            'email'         => $request->user_email,
            'password'      => Hash::make($request->user_password),
            'role'          => 'admin',
            'tenant_id'     => $tenant->id,
            'b_book_access' => $request->boolean('b_book_access'),
        ]);

        return back()->with('success', "User {$request->user_email} added.");
    }

    public function toggleBBookAccess(Tenant $tenant, User $user)
    {
        abort_if($user->tenant_id !== $tenant->id, 403);

        $user->update(['b_book_access' => ! $user->b_book_access]);

        return back()->with('success', $user->b_book_access
            ? "B-Book access granted to {$user->email}."
            : "B-Book access revoked from {$user->email}.");
    }

    public function updatePassword(Request $request, Tenant $tenant, User $user)
    {
        abort_if($user->tenant_id !== $tenant->id, 403);

        $request->validate(['password' => 'required|string|min:6']);

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Password updated for {$user->email}.");
    }

    public function restoreClient(Tenant $tenant, int $clientId)
    {
        \App\Models\BullionClient::withTrashed()
            ->where('tenant_id', $tenant->id)
            ->where('id', $clientId)
            ->restore();

        return back()->with('success', 'Client restored successfully.');
    }

    public function deleteUser(Tenant $tenant, User $user)
    {
        abort_if($user->tenant_id !== $tenant->id, 403);

        // Don't delete if it's the only user
        if (User::where('tenant_id', $tenant->id)->count() <= 1) {
            return back()->with('error', 'Cannot delete the only portal user.');
        }

        $user->delete();
        return back()->with('success', 'User removed.');
    }

    public function checkSlug(Request $request)
    {
        $slug = $request->query('slug', '');
        $taken = Tenant::where('slug', $slug)->exists();
        return response()->json(['available' => !$taken]);
    }

    public function toggle(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);
        return back()->with('success', 'Tenant ' . ($tenant->is_active ? 'activated' : 'deactivated') . '.');
    }
}