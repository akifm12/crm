<?php
// app/Http/Controllers/Tenant/SettingsController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\GoamlStaticConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        $goaml  = GoamlStaticConfig::where('tenant_id', $tenant->id)->first();
        return view('tenant.settings', compact('tenant', 'goaml'));
    }

    public function updateProfile(Request $request)
    {
        $tenant = app('tenant');
        $request->validate([
            'name'             => 'required|string|max:255',
            'contact_email'    => 'required|email',
            'phone'            => 'nullable|string|max:50',
            'address'          => 'nullable|string|max:500',
            'trade_license_no' => 'nullable|string|max:100',
            'dnfbp_reg_no'     => 'nullable|string|max:100',
        ]);

        $tenant->update($request->only([
            'name', 'contact_email', 'phone', 'address',
            'trade_license_no', 'dnfbp_reg_no',
        ]));

        return back()->with('success', 'Company profile updated.');
    }

    public function updateMlro(Request $request)
    {
        $tenant = app('tenant');
        $request->validate([
            'mlro_name'  => 'required|string|max:255',
            'mlro_email' => 'required|email',
            'mlro_phone' => 'nullable|string|max:50',
        ]);

        $tenant->update($request->only(['mlro_name', 'mlro_email', 'mlro_phone']));

        return back()->with('success', 'MLRO details updated.');
    }

    public function uploadLogo(Request $request)
    {
        $tenant = app('tenant');
        $request->validate(['logo' => 'required|image|max:2048|mimes:jpg,jpeg,png,svg']);

        // Delete old logo
        if ($tenant->logo_url && Storage::disk('public')->exists($tenant->logo_url)) {
            Storage::disk('public')->delete($tenant->logo_url);
        }

        $path = $request->file('logo')->store("tenants/{$tenant->id}/logo", 'public');
        $tenant->update(['logo_url' => $path]);

        return back()->with('success', 'Logo updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated.');
    }
}
