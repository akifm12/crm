<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycSubmission;
use App\Models\Tenant;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'tenants'            => Tenant::where('is_active', true)->count(),
            'pending_kyc'        => KycSubmission::where('status', 'pending')->count(),
            'approved_kyc'       => KycSubmission::where('status', 'approved')->count(),
            'under_review'       => KycSubmission::where('status', 'under_review')->count(),
            'recent_submissions' => KycSubmission::with('tenant')
                                        ->latest()
                                        ->take(10)
                                        ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
