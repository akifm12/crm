@extends('layouts.tenant')
@section('title', 'KYC Links — ' . $tenant->name)
@section('page-title', 'KYC Links')
@section('page-subtitle', 'Self-fill links sent to clients, and submissions awaiting your review')

@section('content')

@include('tenant.fill._link_modal')

<a href="{{ route('tenant.clients.index', $tenant->slug) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-5">
    &larr; Back to clients
</a>

{{-- ── Awaiting review ───────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <div class="px-5 py-3 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700">Awaiting your review</h3>
        <p class="text-xs text-gray-400 mt-0.5">Clients who completed their self-fill form — review and approve to activate.</p>
    </div>
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Submitted</th>
                <th class="px-4 py-2.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($pending as $client)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 text-gray-800 font-medium">{{ $client->displayName() }}</td>
                <td class="px-4 py-2.5 text-gray-500 capitalize">{{ str_replace('_', ' ', $client->client_type) }}</td>
                <td class="px-4 py-2.5 text-gray-400">{{ $client->created_at?->format('d M Y, H:i') }}</td>
                <td class="px-4 py-2.5 text-right">
                    <a href="{{ route('tenant.clients.show', [$tenant->slug, $client->id]) }}" class="text-sm text-blue-600 hover:underline font-medium">Review</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">Nothing waiting on you right now.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ── Recent links ──────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700">Recent links sent</h3>
        <p class="text-xs text-gray-400 mt-0.5">Last 10 self-fill links. Expired or already-used links can be reissued below.</p>
    </div>
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Sent</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Expires</th>
                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-2.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tokens as $t)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 text-gray-800">
                    {{ $t->client_name ?: '—' }}
                    @if($t->client_email)<div class="text-xs text-gray-400">{{ $t->client_email }}</div>@endif
                </td>
                <td class="px-4 py-2.5 text-gray-500 capitalize">{{ str_replace('_', ' ', $t->client_type) }}</td>
                <td class="px-4 py-2.5 text-gray-400">{{ $t->created_at->format('d M Y') }}</td>
                <td class="px-4 py-2.5 text-gray-400">{{ $t->expires_at->format('d M Y') }}</td>
                <td class="px-4 py-2.5">
                    @if($t->isUsed())
                        @if($t->client)
                        <a href="{{ route('tenant.clients.show', [$tenant->slug, $t->client->id]) }}" class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-gray-200">Completed</a>
                        @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Used</span>
                        @endif
                    @elseif($t->isExpired())
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">Expired</span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Valid</span>
                    @endif
                </td>
                <td class="px-4 py-2.5 text-right">
                    <form method="POST" action="{{ route('tenant.fill.reissue', [$tenant->slug, $t->token]) }}"
                          onsubmit="return confirm('{{ $t->isUsed() ? 'Issue a new link' : 'Extend this link' }} for {{ addslashes($t->client_name ?: 'this client') }}?')">
                        @csrf
                        <button type="submit" class="text-sm text-blue-600 hover:underline font-medium">
                            {{ $t->isUsed() ? 'Send new link' : 'Extend / resend' }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">No self-fill links sent yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
