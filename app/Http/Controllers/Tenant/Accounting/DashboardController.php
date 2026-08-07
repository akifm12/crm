<?php
// app/Http/Controllers/Tenant/Accounting/DashboardController.php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        return view('tenant.accounting.dashboard', compact('tenant'));
    }
}
