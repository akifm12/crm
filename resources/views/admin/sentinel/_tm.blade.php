{{-- Transaction Monitoring add-on for one client (Sentinel: /api/portal-admin/transaction-monitoring/companies/:id) --}}
@if ($tm)
<div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <h3 class="text-sm font-semibold text-gray-700">Transaction monitoring (add-on)</h3>
            @if ($tm['enabled'])
                <span class="px-2 py-0.5 text-xs font-medium rounded-full border bg-emerald-50 text-emerald-700 border-emerald-200">On{{ $tm['live'] ? '' : ' · test keys only' }}</span>
            @else
                <span class="px-2 py-0.5 text-xs font-medium rounded-full border bg-gray-50 text-gray-600 border-gray-200">Off</span>
            @endif
        </div>
        @if ($tm['enabled'] || $tm['can_enable'])
            <form method="POST" action="{{ route('sentinel-admin.companies.tm', $c['id']) }}"
                  onsubmit="return confirm('{{ $tm['enabled'] ? 'Switch transaction monitoring off? New transactions stop; existing records stay.' : 'Switch transaction monitoring on for this client? It is charged per live transaction.' }}')">
                @csrf
                <input type="hidden" name="enabled" value="{{ $tm['enabled'] ? 0 : 1 }}">
                <button class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $tm['enabled'] ? 'border border-red-200 text-red-600 hover:bg-red-50' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                    {{ $tm['enabled'] ? 'Switch off' : 'Switch on' }}</button>
            </form>
        @endif
    </div>
    <p class="text-xs text-gray-500 leading-relaxed">
        @if ($tm['enabled'])
            Switched on {{ $day($tm['enabled_at']) }}{{ $tm['enabled_by'] ? ' by ' . $tm['enabled_by'] : '' }}.
        @elseif (! $tm['can_enable'])
            For Complete and Corporate API clients. Change the plan first.
        @endif
        Charged per live transaction; test-key transactions are free. The client records transactions in the Sentinel app
        (Transactions tab, one by one or CSV) or through the API. Cash of AED 55,000 or more and split cash are flagged for goAML; other trends for review.
        @unless ($tm['live']) On a trial it works with test keys only. @endunless
    </p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="grid grid-cols-3 gap-3 text-center md:col-span-1">
            <div><p class="text-xl font-semibold text-gray-900">{{ $tm['alerts']['open'] }}</p><p class="text-xs text-gray-500">Open alerts</p></div>
            <div><p class="text-xl font-semibold {{ $tm['alerts']['open_report'] ? 'text-red-600' : 'text-gray-900' }}">{{ $tm['alerts']['open_report'] }}</p><p class="text-xs text-gray-500">goAML open</p></div>
            <div><p class="text-xl font-semibold text-gray-900">{{ $tm['alerts']['total'] }}</p><p class="text-xs text-gray-500">Alerts, all time</p></div>
        </div>
        <div class="md:col-span-2">
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-500"><tr><th class="text-left pb-1">Month</th><th class="text-right pb-1">Live transactions (billable)</th><th class="text-right pb-1">Test</th></tr></thead>
                <tbody>
                @forelse ($tm['usage'] as $u)
                    <tr class="border-t border-gray-100"><td class="py-1 text-gray-700">{{ \Carbon\Carbon::parse($u['month'] . '-01')->format('F Y') }}</td>
                        <td class="py-1 text-right font-medium text-gray-900">{{ number_format($u['live']) }}</td>
                        <td class="py-1 text-right text-gray-500">{{ number_format($u['test']) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="py-2 text-gray-500">No transactions yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
