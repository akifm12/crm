@extends('layouts.tenant')
@section('title', 'Statement of Accounts — ' . $tenant->name)
@section('page-title', 'Statement of Accounts')
@section('page-subtitle', $client ? $client->displayName() : 'Select a client to view their balance')

@section('content')

<form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Client</label>
        <select name="client_id" required class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white min-w-[240px]">
            <option value="">Select client...</option>
            @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ $client && $client->id === $c->id ? 'selected' : '' }}>{{ $c->displayName() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
        <input type="date" name="from" value="{{ $from?->toDateString() }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
        <input type="date" name="as_of" value="{{ $asOf->toDateString() }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
    </div>
    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
        View statement
    </button>
</form>

@if(session('success'))
<div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4 flex items-center justify-between">
    <span>{{ session('success') }}</span>
    @if(session('receipt_payment_id'))
    <a href="{{ route('tenant.accounting.payments.receipt', [$tenant->slug, session('receipt_payment_id')]) }}" target="_blank"
       class="ml-4 px-3 py-1 text-xs font-semibold bg-green-700 text-white rounded hover:bg-green-800 whitespace-nowrap">
        Print Receipt
    </a>
    @endif
</div>
@endif
@if(session('error'))
<div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 mb-4">{{ session('error') }}</div>
@endif

@if($client && $statement)

@php
// ── Merge cash rows and metal rows into one combined stream ──────────────────
// Metal columns only appear if this client has exchange-type metal movements.
$metals = $metalStatement ? $metalStatement['metals'] : [];

// Aggregate metal movements per invoice: invoice_number → metal → {in, out, date, desc}
// (multiple invoice_lines for same invoice+metal are summed)
$metalByInvoice = [];
if ($metalStatement) {
    foreach ($metalStatement['rows'] as $mr) {
        $inv   = $mr['invoice_number'];
        $metal = $mr['metal_type'];
        if (!isset($metalByInvoice[$inv][$metal])) {
            $metalByInvoice[$inv][$metal] = [
                'in' => 0.0, 'out' => 0.0,
                'date' => $mr['date'], 'description' => $mr['description'], 'invoice_type' => $mr['invoice_type'],
            ];
        }
        $metalByInvoice[$inv][$metal]['in']  += $mr['grams_in']  ?? 0;
        $metalByInvoice[$inv][$metal]['out'] += $mr['grams_out'] ?? 0;
    }
}

// Build a running metal-balance timeline keyed by invoice_number so we know
// what the balance is after each metal event, regardless of row order.
$metalBalanceTimeline = []; // invoice_number → [metal → balance_after]
$_runBuild = array_fill_keys($metals, 0.0);
if ($metalStatement) {
    foreach ($metalStatement['rows'] as $mr) {
        $_runBuild[$mr['metal_type']] = $mr['balance'];
        $metalBalanceTimeline[$mr['invoice_number']] = $_runBuild;
    }
}

// Walk cash rows, attach metal data for matching invoices
$metalRunning  = array_fill_keys($metals, 0.0);
$usedMetalInvoices = [];
$combinedRows  = collect();

foreach ($statement['rows'] as $cashRow) {
    $invNum = $cashRow['invoice']?->invoice_number;
    $amount = $cashRow['amount'];

    // First time we see this invoice in cash rows, absorb any metal movement for it
    $moves = [];
    if ($invNum && isset($metalByInvoice[$invNum]) && !in_array($invNum, $usedMetalInvoices)) {
        $moves = $metalByInvoice[$invNum];
        // Advance the running balance to the post-invoice state
        foreach ($metals as $m) {
            if (isset($metalBalanceTimeline[$invNum][$m])) {
                $metalRunning[$m] = $metalBalanceTimeline[$invNum][$m];
            }
        }
        $usedMetalInvoices[] = $invNum;
    }

    $combinedRows->push([
        'date'        => $cashRow['date'],
        'description' => $cashRow['description'],
        'invoice'     => $cashRow['invoice'],
        'cash_dr'     => $amount > 0 ? $amount : null,
        'cash_cr'     => $amount < 0 ? abs($amount) : null,
        'cash_bal'    => $cashRow['balance'],
        'metal_moves' => $moves,           // [metal → {in, out}] — only on first row per invoice
        'metal_bal'   => $metalRunning,    // snapshot of running balance after this row
    ]);
}

// Append any metal-only rows (exchange deposits with no cash counterpart yet)
foreach ($metalByInvoice as $invNum => $moves) {
    if (in_array($invNum, $usedMetalInvoices)) continue;
    $firstMove = reset($moves);
    foreach ($metals as $m) {
        if (isset($metalBalanceTimeline[$invNum][$m])) {
            $metalRunning[$m] = $metalBalanceTimeline[$invNum][$m];
        }
    }
    $combinedRows->push([
        'date'        => $firstMove['date'],
        'description' => $firstMove['description'] ?: ucfirst($firstMove['invoice_type']),
        'invoice'     => null,
        'cash_dr'     => null,
        'cash_cr'     => null,
        'cash_bal'    => $statement['balance'],
        'metal_moves' => $moves,
        'metal_bal'   => $metalRunning,
    ]);
}

$combinedRows = $combinedRows->sortBy('date')->values();
$colSpan = 5 + count($metals) * 3;
@endphp

@php $exportParams = 'client_id='.$client->id.'&as_of='.$asOf->toDateString().($from ? '&from='.$from->toDateString() : ''); @endphp
<div class="flex justify-end gap-2 mb-3">
    <a href="{{ route('tenant.accounting.reports.client-statement.pdf', $tenant->slug) }}?{{ $exportParams }}" target="_blank"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export PDF</a>
    <a href="{{ route('tenant.accounting.reports.client-statement.csv', $tenant->slug) }}?{{ $exportParams }}"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export Excel</a>
</div>

{{-- Header cards --}}
<div class="grid gap-4 mb-5" style="grid-template-columns: auto 1fr {{ count($metals) > 0 ? 'repeat('.count($metals).', auto)' : '' }}">
    <div class="bg-white rounded-xl border border-gray-200 p-4 min-w-[160px]">
        <p class="text-xs text-gray-400 mb-1">Client</p>
        <p class="text-sm font-semibold text-gray-800">{{ $client->displayName() }}</p>
        @if($client->trn_number)<p class="text-xs text-gray-400">TRN: {{ $client->trn_number }}</p>@endif
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <p class="text-xs text-gray-400 mb-1">
            Cash balance {{ $from ? 'from '.$from->format('d M Y').' to' : 'as of' }} {{ $asOf->format('d M Y') }}
        </p>
        <p class="text-2xl font-bold {{ $statement['balance'] > 0 ? 'text-red-600' : ($statement['balance'] < 0 ? 'text-green-600' : 'text-gray-800') }}">
            @if($statement['balance'] < 0)({{ number_format(abs($statement['balance']), 2) }})
            @else{{ number_format($statement['balance'], 2) }}
            @endif
            <span class="text-sm font-normal text-gray-400 ml-1">AED</span>
        </p>
        <p class="text-xs text-gray-400 mt-0.5">
            @if($statement['balance'] > 0) Client owes the business
            @elseif($statement['balance'] < 0) Business owes the client
            @else Settled
            @endif
        </p>
    </div>
    @foreach($metals as $metal)
    @php $mBal = $metalStatement['balances'][$metal] ?? 0; @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-4 min-w-[140px]">
        <p class="text-xs text-gray-400 mb-1">{{ ucfirst($metal) }} held on account</p>
        <p class="text-2xl font-bold {{ $mBal > 0 ? 'text-amber-600' : ($mBal < 0 ? 'text-red-600' : 'text-gray-800') }}">
            @if($mBal < 0)({{ number_format(abs($mBal), 3) }})
            @else{{ number_format($mBal, 3) }}
            @endif
            <span class="text-sm font-normal text-gray-400 ml-1">g</span>
        </p>
    </div>
    @endforeach
</div>

{{-- Combined ledger table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <th class="px-4 py-3 whitespace-nowrap">Date</th>
                <th class="px-4 py-3">Description</th>
                <th class="px-4 py-3 text-right whitespace-nowrap">Cash Dr</th>
                <th class="px-4 py-3 text-right whitespace-nowrap">Cash Cr</th>
                <th class="px-4 py-3 text-right whitespace-nowrap">Cash Bal</th>
                @foreach($metals as $metal)
                <th class="px-4 py-3 text-right whitespace-nowrap border-l border-gray-100">{{ ucfirst($metal) }} (g) In</th>
                <th class="px-4 py-3 text-right whitespace-nowrap">{{ ucfirst($metal) }} (g) Out</th>
                <th class="px-4 py-3 text-right whitespace-nowrap">{{ ucfirst($metal) }} Bal</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @if($from)
            <tr class="bg-gray-50 text-xs font-semibold text-gray-500">
                <td class="px-4 py-2 whitespace-nowrap">{{ $from->format('d M Y') }}</td>
                <td class="px-4 py-2 italic">Opening balance</td>
                <td class="px-4 py-2"></td>
                <td class="px-4 py-2"></td>
                <td class="px-4 py-2 text-right font-mono font-semibold whitespace-nowrap
                    {{ $statement['opening_balance'] < 0 ? 'text-green-600' : ($statement['opening_balance'] > 0 ? 'text-gray-700' : 'text-gray-400') }}">
                    @if($statement['opening_balance'] < 0)({{ number_format(abs($statement['opening_balance']), 2) }})
                    @elseif($statement['opening_balance'] > 0){{ number_format($statement['opening_balance'], 2) }}
                    @else —
                    @endif
                </td>
                @foreach($metals as $metal)
                @php $ob = $metalStatement['opening_balances'][$metal] ?? 0; @endphp
                <td class="px-4 py-2 border-l border-gray-100"></td>
                <td class="px-4 py-2"></td>
                <td class="px-4 py-2 text-right font-mono font-semibold whitespace-nowrap
                    {{ $ob < 0 ? 'text-red-600' : ($ob > 0 ? 'text-gray-700' : 'text-gray-400') }}">
                    @if($ob < 0)({{ number_format(abs($ob), 3) }})
                    @elseif($ob > 0){{ number_format($ob, 3) }}
                    @else —
                    @endif
                </td>
                @endforeach
            </tr>
            @endif
            @forelse($combinedRows as $row)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">{{ $row['date']->format('d M Y') }}</td>
                <td class="px-4 py-2.5 text-gray-700">
                    @if($row['invoice'])
                    <a href="{{ route('tenant.accounting.invoices.show', [$tenant->slug, $row['invoice']->id]) }}" class="text-blue-600 hover:underline">{{ $row['description'] }}</a>
                    @else
                    {{ $row['description'] }}
                    @endif
                </td>
                <td class="px-4 py-2.5 text-right font-mono text-gray-700">
                    {{ $row['cash_dr'] !== null ? number_format($row['cash_dr'], 2) : '' }}
                </td>
                <td class="px-4 py-2.5 text-right font-mono text-gray-700">
                    {{ $row['cash_cr'] !== null ? number_format($row['cash_cr'], 2) : '' }}
                </td>
                <td class="px-4 py-2.5 text-right font-mono font-semibold whitespace-nowrap
                    {{ $row['cash_bal'] < 0 ? 'text-green-600' : ($row['cash_bal'] > 0 ? 'text-gray-800' : 'text-gray-400') }}">
                    @if($row['cash_bal'] < 0)({{ number_format(abs($row['cash_bal']), 2) }})
                    @elseif($row['cash_bal'] > 0){{ number_format($row['cash_bal'], 2) }}
                    @else —
                    @endif
                </td>
                @foreach($metals as $metal)
                @php
                    $mv  = $row['metal_moves'][$metal] ?? null;
                    $bal = $row['metal_bal'][$metal]   ?? 0;
                @endphp
                <td class="px-4 py-2.5 text-right font-mono text-green-700 border-l border-gray-100">
                    {{ $mv && $mv['in'] > 0 ? number_format($mv['in'], 3) : '' }}
                </td>
                <td class="px-4 py-2.5 text-right font-mono text-red-500">
                    {{ $mv && $mv['out'] > 0 ? number_format($mv['out'], 3) : '' }}
                </td>
                <td class="px-4 py-2.5 text-right font-mono font-semibold whitespace-nowrap
                    {{ $bal < 0 ? 'text-red-600' : ($bal > 0 ? 'text-gray-800' : 'text-gray-400') }}">
                    @if($bal < 0)({{ number_format(abs($bal), 3) }})
                    @elseif($bal > 0){{ number_format($bal, 3) }}
                    @else —
                    @endif
                </td>
                @endforeach
            </tr>
            @empty
            <tr><td colspan="{{ $colSpan }}" class="px-4 py-6 text-center text-sm text-gray-400">No posted transactions for this client yet.</td></tr>
            @endforelse
        </tbody>
        @if($combinedRows->isNotEmpty())
        <tfoot>
            <tr class="border-t-2 border-gray-200 bg-gray-50 font-semibold text-sm">
                <td colspan="2" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Closing balance</td>
                <td class="px-4 py-3"></td>
                <td class="px-4 py-3"></td>
                <td class="px-4 py-3 text-right font-mono whitespace-nowrap
                    {{ $statement['balance'] < 0 ? 'text-green-600' : ($statement['balance'] > 0 ? 'text-gray-800' : 'text-gray-400') }}">
                    @if($statement['balance'] < 0)({{ number_format(abs($statement['balance']), 2) }})
                    @elseif($statement['balance'] > 0){{ number_format($statement['balance'], 2) }}
                    @else —
                    @endif
                </td>
                @foreach($metals as $metal)
                @php $finalBal = $metalStatement['balances'][$metal] ?? 0; @endphp
                <td class="border-l border-gray-100"></td>
                <td></td>
                <td class="px-4 py-3 text-right font-mono whitespace-nowrap
                    {{ $finalBal < 0 ? 'text-red-600' : ($finalBal > 0 ? 'text-gray-800' : 'text-gray-400') }}">
                    @if($finalBal < 0)({{ number_format(abs($finalBal), 3) }})
                    @elseif($finalBal > 0){{ number_format($finalBal, 3) }}
                    @else —
                    @endif
                </td>
                @endforeach
            </tr>
        </tfoot>
        @endif
    </table>
    </div>
</div>

@if($unlinkedDeposits->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 p-5 mt-5 max-w-3xl">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Unapplied deposits / margin</h3>
    <table class="w-full text-sm">
        <tbody class="divide-y divide-gray-50">
            @foreach($unlinkedDeposits as $deposit)
            <tr>
                <td class="py-2 text-gray-500">{{ $deposit->payment_date->format('d M Y') }}</td>
                <td class="py-2 text-gray-500">{{ $deposit->purpose ?: ucfirst($deposit->direction) }}</td>
                <td class="py-2 text-right font-mono">{{ number_format($deposit->amount, 2) }}</td>
                <td class="py-2 text-right">
                    <form method="POST" action="{{ route('tenant.accounting.deposits.apply', [$tenant->slug, $deposit->id]) }}" class="inline-flex items-center gap-1">
                        @csrf
                        <select name="invoice_id" required class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white">
                            <option value="">Apply to...</option>
                            @foreach($clientInvoices->whereIn('status', ['posted', 'pending_fix']) as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->invoice_number }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="text-xs text-blue-600 hover:underline">Apply</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="bg-white rounded-xl border border-gray-200 p-5 mt-5 max-w-3xl"
     x-data="{ hasInvoice: false, invoiceType: '' }">
    <h3 class="text-sm font-semibold text-gray-700 mb-3">Record payment / deposit</h3>
    <form method="POST" action="{{ route('tenant.accounting.clients.payments.store', [$tenant->slug, $client->id]) }}" class="flex flex-wrap gap-2 items-end">
        @csrf
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Invoice</label>
            <select name="invoice_id" @change="hasInvoice = $event.target.value !== ''; invoiceType = $event.target.selectedOptions[0]?.dataset?.type || ''"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white">
                <option value="">No invoice (margin/deposit)</option>
                @foreach($clientInvoices->whereIn('status', ['posted', 'pending_fix']) as $inv)
                <option value="{{ $inv->id }}" data-type="{{ $inv->invoice_type }}">{{ $inv->invoice_number }} ({{ ucfirst($inv->status === 'pending_fix' ? 'pending fix' : $inv->status) }})</option>
                @endforeach
            </select>
        </div>
        <div x-show="!hasInvoice">
            <label class="block text-xs font-medium text-gray-600 mb-1">Direction</label>
            <select name="direction" :required="!hasInvoice" class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white">
                <option value="in">Receipt from client</option>
                <option value="out">Payment to client</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
            <input type="date" name="payment_date" required value="{{ now()->toDateString() }}" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Amount</label>
            <input type="number" step="0.01" min="0.01" name="amount" required class="px-3 py-2 text-sm border border-gray-200 rounded-lg w-32">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Method</label>
            <select name="method" class="px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank transfer</option>
                <option value="card">Card</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Reference</label>
            <input type="text" name="reference" class="px-3 py-2 text-sm border border-gray-200 rounded-lg">
        </div>
        <div x-show="!hasInvoice">
            <label class="block text-xs font-medium text-gray-600 mb-1">Purpose</label>
            <input type="text" name="purpose" value="Margin deposit" class="px-3 py-2 text-sm border border-gray-200 rounded-lg w-36">
        </div>
        <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            Record
        </button>
    </form>
</div>

@endif

@endsection
