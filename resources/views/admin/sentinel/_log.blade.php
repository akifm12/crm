{{-- Sentinel admin activity (sentinel_admin_log). $log, $showClient --}}
@php
    $actions = [
        'client.create' => 'Created client', 'client.update' => 'Edited client', 'client.plan' => 'Changed plan',
        'client.delete' => 'Deleted client', 'key.create' => 'Created API key', 'key.revoke' => 'Revoked API key',
        'user.create' => 'Added user', 'user.update' => 'Edited user', 'user.delete' => 'Deleted user',
        'user.convert' => 'Converted trial', 'user.password_link' => 'Sent password link', 'key_request.status' => 'Updated API request',
    ];
    $summary = function ($e) {
        $d = $e['details'] ?? [];
        return match ($e['action']) {
            'client.plan'  => ($d['from'] ?? '—') . ' → ' . ($d['to'] ?? '') . (! empty($d['expiry']) ? ', expires ' . $d['expiry'] : ''),
            'key.create'   => ($d['mode'] ?? '') . ' key ' . ($d['key_id'] ?? ''),
            'key.revoke'   => $d['key_id'] ?? '',
            'user.convert' => ($d['email'] ?? '') . ' → ' . ($d['name'] ?? '') . ' (' . ($d['plan'] ?? '') . ')',
            default        => collect($d)->except(['companyId'])->map(fn ($v, $k) => is_bool($v) ? "$k: " . ($v ? 'on' : 'off') : (is_scalar($v) ? "$k: $v" : null))->filter()->implode(', '),
        };
    };
@endphp
<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
            <tr><th class="px-4 py-3 text-left">When</th><th class="px-4 py-3 text-left">Who</th><th class="px-4 py-3 text-left">What</th>
                @if ($showClient)<th class="px-4 py-3 text-left">Client</th>@endif<th class="px-4 py-3 text-left">Details</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
        @forelse ($log as $e)
            <tr>
                <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($e['at'])->timezone('Asia/Dubai')->format('j M Y H:i') }}</td>
                <td class="px-4 py-2.5 text-gray-600">{{ $e['actor'] }}</td>
                <td class="px-4 py-2.5 font-medium text-gray-900">{{ $actions[$e['action']] ?? $e['action'] }}</td>
                @if ($showClient)
                    <td class="px-4 py-2.5">@if (! empty($e['company_id']) && ! empty($e['company_name']))<a href="{{ route('sentinel-admin.company', $e['company_id']) }}" class="text-blue-600 hover:underline">{{ $e['company_name'] }}</a>@else — @endif</td>
                @endif
                <td class="px-4 py-2.5 text-gray-600 text-xs">{{ $summary($e) }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Nothing yet: changes made here appear in this log.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
