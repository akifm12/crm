@extends('layouts.admin')
@section('title', 'Edit Tenant — ' . $tenant->name)

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900">Edit — {{ $tenant->name }}</h1>
    <p class="text-sm text-gray-500 mt-0.5">
        <a href="{{ $tenant->portalUrl() }}" target="_blank" class="text-blue-600 hover:underline font-mono text-xs">{{ $tenant->portalUrl() }}</a>
    </p>
</div>

<form method="POST" action="{{ route('kyc.tenants.update', $tenant->id) }}" class="max-w-2xl space-y-5">
@csrf @method('PATCH')

<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Portal details</h3>
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Company name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Portal slug</label>
                <input type="text" value="{{ $tenant->slug }}" disabled
                       class="w-full px-3 py-2 text-sm border border-gray-100 rounded-lg bg-gray-50 text-gray-400">
                <p class="text-xs text-gray-400 mt-1">Slug cannot be changed after creation</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Business sector <span class="text-red-500">*</span></label>
                <select name="business_type" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    @foreach($sectors as $key => $label)
                    <option value="{{ $key }}" {{ old('business_type', $tenant->business_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Contact email <span class="text-red-500">*</span></label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">DNFBP registration number</label>
                <input type="text" name="dnfbp_reg_no" value="{{ old('dnfbp_reg_no', $tenant->dnfbp_reg_no) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">VAT TRN</label>
                <input type="text" name="vat_trn" value="{{ old('vat_trn', $tenant->vat_trn) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">Shown on invoice PDFs for this portal</p>
            </div>
        </div>
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
            <input type="checkbox" name="is_active" value="1" id="is_active" {{ $tenant->is_active ? 'checked' : '' }}
                   class="rounded border-gray-300 text-blue-600">
            <label for="is_active" class="text-sm text-gray-700">Portal active — tenants can log in</label>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Accounting Modules</h3>
    <div class="space-y-2">
        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
            <input type="checkbox" name="module_bullion_accounting" value="1" id="module_bullion_accounting"
                   {{ $tenant->hasModule('bullion_accounting') ? 'checked' : '' }}
                   class="mt-0.5 rounded border-gray-300 text-blue-600">
            <div>
                <label for="module_bullion_accounting" class="text-sm font-medium text-gray-700">Bullion Accounting</label>
                <p class="text-xs text-gray-400 mt-0.5">Gold/silver invoicing, metal inventory, making charges, trial balance &amp; balance sheet</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
            <input type="checkbox" name="module_general_accounting" value="1" id="module_general_accounting"
                   {{ $tenant->hasModule('general_accounting') ? 'checked' : '' }}
                   class="mt-0.5 rounded border-gray-300 text-blue-600">
            <div>
                <label for="module_general_accounting" class="text-sm font-medium text-gray-700">General Accounting</label>
                <p class="text-xs text-gray-400 mt-0.5">Standard service/product invoicing, bills, journal, VAT return — suitable for any business</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
            <input type="checkbox" name="module_real_estate_accounting" value="1" id="module_real_estate_accounting"
                   {{ $tenant->hasModule('real_estate_accounting') ? 'checked' : '' }}
                   class="mt-0.5 rounded border-gray-300 text-blue-600">
            <div>
                <label for="module_real_estate_accounting" class="text-sm font-medium text-gray-700">Real Estate Accounting</label>
                <p class="text-xs text-gray-400 mt-0.5">Rental income, property expenses, maintenance bills, tenant management</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-gray-900 rounded-lg" x-data="{ a: {{ $tenant->hasModule('bullion_accounting') ? 'true' : 'false' }} }">
            <input type="checkbox" name="module_b_book" value="1" id="module_b_book" :disabled="!a"
                   {{ $tenant->hasModule('b_book') ? 'checked' : '' }}
                   class="mt-0.5 rounded border-gray-600 text-amber-500 disabled:opacity-40">
            <div>
                <label for="module_b_book" class="text-sm font-medium text-amber-400">B-Book</label>
                <p class="text-xs text-gray-400 mt-0.5">Consolidated ledger + OTC dealings (no VAT, no formal invoicing) — separate login portal, own theme. Requires Bullion Accounting.</p>
                <p x-show="!a" class="text-xs text-red-400 mt-1">Enable Bullion Accounting first.</p>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center justify-between">
    <a href="{{ route('kyc.tenants') }}"
       class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        Cancel
    </a>
    <button type="submit"
            class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Save changes
    </button>
</div>

</form>

{{-- ── Portal Users ──────────────────────────────────────────────────── --}}
<div class="max-w-2xl mt-6 space-y-4">

    @if(session('success'))
    <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Archived clients --}}
    @php $archivedClients = \App\Models\BullionClient::withTrashed()->where('tenant_id', $tenant->id)->whereNotNull('deleted_at')->orderByDesc('deleted_at')->get(); @endphp
    @if($archivedClients->count() > 0)
    <div class="bg-white rounded-xl border border-gray-200 mb-4">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Archived clients <span class="text-xs font-normal text-gray-400">({{ $archivedClients->count() }} — not visible to tenant)</span></h3>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($archivedClients as $ac)
            <div class="px-5 py-3 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $ac->displayName() }}</p>
                    <p class="text-xs text-gray-400">{{ ucfirst(str_replace('_',' ',$ac->client_type)) }} · Archived {{ $ac->deleted_at->format('d M Y') }}</p>
                </div>
                <form method="POST" action="{{ route('kyc.tenants.clients.restore', [$tenant->id, $ac->id]) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-xs text-blue-600 hover:underline">Restore</button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Portal users</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($users as $u)
            <div class="px-5 py-3 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $u->name }}</p>
                    <p class="text-xs text-gray-400">{{ $u->email }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($tenant->hasModule('b_book'))
                    <form method="POST" action="{{ route('kyc.tenants.users.bbook', [$tenant->id, $u->id]) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg {{ $u->b_book_access ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                            {{ $u->b_book_access ? '★ B-Book access' : 'Grant B-Book' }}
                        </button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('kyc.tenants.users.password', [$tenant->id, $u->id]) }}"
                          class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="text" name="password" placeholder="New password" minlength="6" required
                               class="px-2 py-1.5 text-xs border border-gray-200 rounded-lg w-32 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Update</button>
                    </form>
                    <form method="POST" action="{{ route('kyc.tenants.users.delete', [$tenant->id, $u->id]) }}"
                          onsubmit="return confirm('Remove this user?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">Remove</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-5 py-4 text-sm text-gray-400">No portal users yet.</div>
            @endforelse
        </div>
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
            <p class="text-xs font-semibold text-gray-500 mb-3">Add portal user</p>
            <form method="POST" action="{{ route('kyc.tenants.users.add', $tenant->id) }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-3 gap-3">
                    <input type="text" name="user_name" placeholder="Full name" required
                           class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="email" name="user_email" placeholder="Email" required
                           class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="user_password" placeholder="Password" minlength="6" required
                           class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add user
                </button>
            </form>
        </div>
    </div>
</div>
@endsection