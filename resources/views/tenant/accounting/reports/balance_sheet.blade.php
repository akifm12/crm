@extends('layouts.tenant')
@section('title', 'Balance Sheet — ' . $tenant->name)
@section('page-title', 'Balance Sheet')
@section('page-subtitle', 'As of ' . $asOf->format('d M Y'))

@section('content')

<form method="GET" class="mb-4 flex items-center gap-2">
    <label class="text-xs font-medium text-gray-600">As of</label>
    <input type="date" name="as_of" value="{{ $asOf->toDateString() }}"
           class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg" onchange="this.form.submit()">
    <a href="{{ route('tenant.accounting.reports.balance-sheet.pdf', $tenant->slug) }}?as_of={{ $asOf->toDateString() }}" target="_blank"
       class="ml-auto px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export PDF</a>
    <a href="{{ route('tenant.accounting.reports.balance-sheet.csv', $tenant->slug) }}?as_of={{ $asOf->toDateString() }}"
       class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Export Excel</a>
</form>

@unless($balanced)
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
    Assets do not equal Liabilities + Equity — please review recent journal entries.
</div>
@endunless

<div class="grid grid-cols-2 gap-5 max-w-4xl">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Assets</h3>
        <table class="w-full text-sm">
            <tbody class="divide-y divide-gray-50">
                @foreach($assets as $row)
                <tr>
                    <td class="py-1.5 text-gray-600">{{ $row['account']->name }}</td>
                    <td class="py-1.5 text-right font-mono">{{ number_format($row['balance'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-gray-200 font-semibold text-gray-800">
                    <td class="pt-2">Total Assets</td>
                    <td class="pt-2 text-right font-mono">{{ number_format($totalAssets, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Liabilities</h3>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-50">
                    @foreach($liabilities as $row)
                    <tr>
                        <td class="py-1.5 text-gray-600">{{ $row['account']->name }}</td>
                        <td class="py-1.5 text-right font-mono">{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200 font-semibold text-gray-800">
                        <td class="pt-2">Total Liabilities</td>
                        <td class="pt-2 text-right font-mono">{{ number_format($totalLiabilities, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Equity</h3>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-50">
                    @foreach($equity as $row)
                    <tr>
                        <td class="py-1.5 text-gray-600">{{ $row['account']->name }}</td>
                        <td class="py-1.5 text-right font-mono">{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td class="py-1.5 text-gray-600">Current Period Earnings</td>
                        <td class="py-1.5 text-right font-mono">{{ number_format($netIncome, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200 font-semibold text-gray-800">
                        <td class="pt-2">Total Equity</td>
                        <td class="pt-2 text-right font-mono">{{ number_format($totalEquity + $netIncome, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection
