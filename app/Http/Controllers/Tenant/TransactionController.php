<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ClientTransaction;
use Illuminate\Http\Request;

/** All client transactions in one list, filterable, with CSV export. */
class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('tenant');

        $request->validate([
            'from' => 'nullable|date', 'to' => 'nullable|date',
            'q' => 'nullable|string|max:100', 'type' => 'nullable|string|max:50',
            'min' => 'nullable|numeric', 'max' => 'nullable|numeric',
        ]);

        $query = $this->filtered($request, $tenant->id);

        if ($request->query('export') === 'csv') {
            return $this->csv($query->get());
        }

        $total = (clone $query)->sum('invoice_amount');
        $count = (clone $query)->count();
        $transactions = $query->paginate(50)->withQueryString();

        $types = ClientTransaction::where('tenant_id', $tenant->id)->whereNotNull('transaction_type')
            ->distinct()->orderBy('transaction_type')->pluck('transaction_type');

        return view('tenant.transactions.index', compact('tenant', 'transactions', 'total', 'count', 'types'));
    }

    private function filtered(Request $request, int $tenantId)
    {
        $q = ClientTransaction::where('tenant_id', $tenantId)
            ->with(['client' => fn ($c) => $c->withTrashed()])
            ->orderByDesc('visit_date')->orderByDesc('id');

        if ($request->filled('from'))  $q->whereDate('visit_date', '>=', $request->from);
        if ($request->filled('to'))    $q->whereDate('visit_date', '<=', $request->to);
        if ($request->filled('type'))  $q->where('transaction_type', $request->type);
        if ($request->filled('min'))   $q->where('invoice_amount', '>=', $request->min);
        if ($request->filled('max'))   $q->where('invoice_amount', '<=', $request->max);

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $q->where(function ($w) use ($term) {
                $w->where('invoice_number', 'like', $term)
                  ->orWhere('notes', 'like', $term)
                  ->orWhereHas('client', fn ($c) => $c->withTrashed()
                      ->where(fn ($n) => $n->where('company_name', 'like', $term)->orWhere('full_name', 'like', $term)));
            });
        }

        return $q;
    }

    private function csv($rows)
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // Cells starting with = + - @ would run as formulas in Excel; prefix them.
            $safe = fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v;
            fputcsv($out, ['Date', 'Client', 'Type', 'Invoice no.', 'Amount', 'Notes']);
            foreach ($rows as $t) {
                fputcsv($out, [
                    $t->visit_date?->format('Y-m-d'),
                    $safe($t->client?->displayName()),
                    $safe($t->transaction_type),
                    $safe($t->invoice_number),
                    $t->invoice_amount,
                    $safe($t->notes),
                ]);
            }
            fclose($out);
        }, 'transactions-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
