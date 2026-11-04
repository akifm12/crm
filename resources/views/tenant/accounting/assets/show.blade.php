@extends('layouts.tenant')
@section('title', $asset->asset_number . ' — ' . $tenant->name)
@section('page-title', $asset->name)
@section('page-subtitle', $asset->asset_number . ' · ' . ($asset->category?->name ?? 'Uncategorised'))

@section('content')
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
@endif

{{-- Summary --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
    @foreach([['Purchase Cost',number_format($asset->purchase_cost,2),''],['Accum. Depreciation',number_format($asset->accumulated_depreciation,2),'text-red-600'],['Net Book Value',number_format($asset->net_book_value,2),'text-blue-700'],['Useful Life',$asset->useful_life_years.' yrs','']] as [$label,$val,$cls])
    <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
        <p class="text-xs text-gray-500 mb-1">{{ $label }}</p>
        <p class="font-semibold font-mono {{ $cls }}">{{ $val }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">

        {{-- Details --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold">Asset Details</h3>
                @php $sc = ['active'=>'bg-green-100 text-green-800','disposed'=>'bg-gray-100 text-gray-500','fully_depreciated'=>'bg-yellow-100 text-yellow-800']; @endphp
                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $sc[$asset->status] ?? 'bg-gray-100 text-gray-600' }}">{{ str_replace('_',' ',ucfirst($asset->status)) }}</span>
            </div>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-gray-500">Purchase Date</dt><dd>{{ $asset->purchase_date }}</dd></div>
                <div><dt class="text-xs text-gray-500">In Service</dt><dd>{{ $asset->in_service_date }}</dd></div>
                <div><dt class="text-xs text-gray-500">Serial No</dt><dd>{{ $asset->serial_number ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Location</dt><dd>{{ $asset->location ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Assigned To</dt><dd>{{ $asset->assigned_to ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Supplier</dt><dd>{{ $asset->supplier?->name ?? '—' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Depr. Method</dt><dd>{{ str_replace('_',' ',ucfirst($asset->depreciation_method)) }}</dd></div>
                <div><dt class="text-xs text-gray-500">Depr. Rate</dt><dd>{{ round($asset->depreciation_rate * 100, 2) }}% p.a.</dd></div>
                <div><dt class="text-xs text-gray-500">Asset GL</dt><dd class="font-mono text-xs">{{ $asset->assetAccount?->code }} — {{ $asset->assetAccount?->name }}</dd></div>
                <div><dt class="text-xs text-gray-500">Accum. Depr. GL</dt><dd class="font-mono text-xs">{{ $asset->accumDeprAccount?->code }} — {{ $asset->accumDeprAccount?->name }}</dd></div>
            </dl>
        </div>

        {{-- Depreciation schedule --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold">Depreciation Schedule</h3>
                <span class="text-xs text-gray-500">{{ $asset->depreciationLines->count() }} periods posted</span>
            </div>
            <table class="w-full text-xs">
                <thead class="bg-gray-50 text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Period</th>
                        <th class="px-4 py-2 text-right">Depr. Amt</th>
                        <th class="px-4 py-2 text-right">Accum. Depr.</th>
                        <th class="px-4 py-2 text-right">NBV</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($asset->depreciationLines->sortByDesc('period_end') as $line)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $line->period_start }} – {{ $line->period_end }}</td>
                        <td class="px-4 py-2 text-right font-mono">{{ number_format($line->depreciation_amount, 2) }}</td>
                        <td class="px-4 py-2 text-right font-mono text-red-600">{{ number_format($line->accumulated_depreciation, 2) }}</td>
                        <td class="px-4 py-2 text-right font-mono font-semibold text-blue-700">{{ number_format($line->net_book_value, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No depreciation posted yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Actions --}}
    <div class="space-y-4">
        <a href="{{ route('tenant.accounting.assets.edit', [$tenant->slug, $asset->id]) }}" class="block w-full text-center px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Edit Asset</a>

        @if($asset->status === 'active')
        <details class="bg-white border border-gray-200 rounded-xl">
            <summary class="px-4 py-3 text-sm font-medium cursor-pointer text-red-700">Dispose Asset ▾</summary>
            <form method="POST" action="{{ route('tenant.accounting.assets.dispose', [$tenant->slug, $asset->id]) }}" class="p-4 pt-2 space-y-3">
                @csrf
                <div><label class="block text-xs text-gray-500 mb-1">Disposal Date</label><input type="date" name="disposal_date" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Proceeds Amount</label><input type="number" step="0.01" name="proceeds_amount" value="0" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Proceeds Account (DR)</label>
                    <select name="proceeds_account_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-1.5">
                        <option value="">— None —</option>
                        @foreach(\App\Models\ChartOfAccount::where('tenant_id',app('tenant')->id)->whereIn('account_type',['ASSET'])->orderBy('code')->get() as $a)
                        <option value="{{ $a->id }}">{{ $a->code }} — {{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" onclick="return confirm('Record disposal and post journal entry?')" class="w-full py-1.5 text-sm font-medium bg-red-700 text-white rounded-lg hover:bg-red-800">Confirm Disposal</button>
            </form>
        </details>
        @endif

        <form method="POST" action="{{ route('tenant.accounting.assets.destroy', [$tenant->slug, $asset->id]) }}" onsubmit="return confirm('Delete this asset?')">
            @csrf @method('DELETE')
            <button class="w-full py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Delete Asset</button>
        </form>
    </div>
</div>
@endsection
