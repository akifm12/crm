<?php
// app/Http/Controllers/BBook/OtcDealController.php

namespace App\Http\Controllers\BBook;

use App\Http\Controllers\Controller;
use App\Models\OtcDeal;

class OtcDealController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');

        $deals = OtcDeal::where('tenant_id', $tenant->id)
            ->orderByDesc('deal_date')
            ->orderByDesc('id')
            ->paginate(30);

        return view('bbook.deals.index', compact('tenant', 'deals'));
    }
}
