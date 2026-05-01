@extends('layouts.tenant')
@section('title', $client->displayName() . ' — ' . $tenant->name)
@section('page-title', $client->displayName())
@section('page-subtitle', ucfirst($client->client_type) . ' · Added ' . $client->created_at->format('d M Y'))

@section('content')

{{-- ── HEADER CARD ──────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <div class="flex flex-wrap items-start justify-between gap-4">

        {{-- Identity --}}
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center text-xl font-bold text-blue-700 flex-shrink-0">
                {{ strtoupper(substr($client->displayName(), 0, 1)) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $client->displayName() }}</h2>
                @if($client->client_type === 'corporate')
                    <p class="text-sm text-gray-500">{{ $client->trade_license_no ?? 'No licence on file' }}
                        @if($client->legal_form) · {{ $client->legal_form }} @endif
                    </p>
                @else
                    <p class="text-sm text-gray-500">{{ $client->nationality ?? '' }}
                        @if($client->dob) · DOB {{ $client->dob->format('d M Y') }} @endif
                    </p>
                @endif
                <p class="text-xs text-gray-400 mt-0.5">{{ $client->email ?? '' }} {{ $client->phone ? '· '.$client->phone : '' }}</p>
            </div>
        </div>

        {{-- Status badges --}}
        <div class="flex flex-wrap gap-2 items-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $client->statusBadgeColor() }}">
                {{ ucfirst($client->status) }}
            </span>
            @if($client->risk_rating)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $client->riskBadgeColor() }}">
                {{ ucfirst($client->risk_rating) }} risk
            </span>
            @endif
            @if($client->cdd_type === 'enhanced')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-700">
                EDD
            </span>
            @endif
            @php
                $screenColors = ['clear'=>'bg-green-100 text-green-700','match'=>'bg-red-100 text-red-700','pending'=>'bg-amber-100 text-amber-700','not_screened'=>'bg-gray-100 text-gray-500'];
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $screenColors[$client->screening_status] ?? 'bg-gray-100 text-gray-500' }}">
                {{ ucfirst(str_replace('_', ' ', $client->screening_status)) }}
            </span>
        </div>

        {{-- Action buttons --}}
        <div class="flex gap-2">
            <a href="{{ route('tenant.screening', $tenant->slug) }}?client={{ $client->id }}"
               class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                Run screening
            </a>
            <button class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Edit
            </button>
        </div>
    </div>

    {{-- Alert banners --}}
    @if($client->isLicenseExpired())
    <div class="mt-4 flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Trade licence expired on {{ $client->trade_license_expiry->format('d M Y') }}. Renewal required.
    </div>
    @elseif($client->isLicenseExpiringSoon())
    <div class="mt-4 flex items-center gap-2 p-3 bg-orange-50 border border-orange-200 rounded-lg text-sm text-orange-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Trade licence expiring on {{ $client->trade_license_expiry->format('d M Y') }}.
    </div>
    @endif
    @if($client->isReviewDue())
    <div class="mt-2 flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        KYC review {{ $client->next_review_date->isPast() ? 'was due on' : 'due' }} {{ $client->next_review_date->format('d M Y') }}.
    </div>
    @endif
</div>

{{-- ── TABS ─────────────────────────────────────────────────────────────────── --}}
<div x-data="{ tab: 'overview' }">

    {{-- Tab bar --}}
    <div class="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 mb-5 overflow-x-auto">
        @foreach([
            ['overview',      'Overview'],
            ['profile',       'Profile'],
            ['documents',     'Documents (' . $documents->count() . ')'],
            ['screening',     'Screening'],
            ['risk',          'Risk & CDD'],
            ['declarations',  'Declarations'],
        ] as [$key, $label])
        <button type="button" @click="tab='{{ $key }}'"
                :class="tab==='{{ $key }}'
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap flex-shrink-0">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ── TAB: OVERVIEW ────────────────────────────────────────────────────── --}}
    <div x-show="tab==='overview'">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Key details --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Key details</h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-2 gap-4">
                        @if($client->client_type === 'corporate')
                        @foreach([
                            ['Company name', $client->company_name],
                            ['Trade licence', $client->trade_license_no],
                            ['Licence expiry', $client->trade_license_expiry?->format('d M Y')],
                            ['Legal form', $client->legal_form],
                            ['TRN', $client->trn_number],
                            ['Ejari', $client->ejari_number],
                            ['Country', $client->country_of_incorporation],
                            ['Business activity', $client->business_activity],
                            ['Email', $client->email],
                            ['Phone', $client->phone],
                        ] as [$k, $v])
                        <div>
                            <dt class="text-xs font-medium text-gray-400">{{ $k }}</dt>
                            <dd class="text-sm text-gray-800 mt-0.5">{{ $v ?? '—' }}</dd>
                        </div>
                        @endforeach
                        @else
                        @foreach([
                            ['Full name', $client->full_name],
                            ['Arabic name', $client->name_arabic],
                            ['Nationality', $client->nationality],
                            ['Date of birth', $client->dob?->format('d M Y')],
                            ['Passport no.', $client->passport_number],
                            ['Passport expiry', $client->passport_expiry?->format('d M Y')],
                            ['Emirates ID', $client->eid_number],
                            ['EID expiry', $client->eid_expiry?->format('d M Y')],
                            ['Occupation', $client->occupation],
                            ['Employer', $client->employer_name],
                            ['Email', $client->email],
                            ['Phone', $client->phone],
                        ] as [$k, $v])
                        <div>
                            <dt class="text-xs font-medium text-gray-400">{{ $k }}</dt>
                            <dd class="text-sm text-gray-800 mt-0.5">{{ $v ?? '—' }}</dd>
                        </div>
                        @endforeach
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Compliance summary --}}
            <div class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Compliance snapshot</h3>
                    <div class="space-y-2.5">
                        @foreach([
                            ['Risk rating',    ucfirst($client->risk_rating ?? 'Unrated'),   $client->risk_rating ? ($client->riskBadgeColor()) : 'bg-gray-100 text-gray-500'],
                            ['CDD type',       ucfirst($client->cdd_type ?? '—'),             'bg-gray-100 text-gray-600'],
                            ['Screening',      ucfirst(str_replace('_',' ',$client->screening_status)), $screenColors[$client->screening_status] ?? 'bg-gray-100 text-gray-500'],
                            ['Next review',    $client->next_review_date?->format('d M Y') ?? 'Not set', 'bg-gray-100 text-gray-600'],
                            ['Declarations',   $client->allDeclarationsSigned() ? 'All complete' : 'Incomplete', $client->allDeclarationsSigned() ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'],
                            ['Master decl.',   $client->decl_master_signed ? 'Signed & uploaded' : 'Pending', $client->decl_master_signed ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'],
                        ] as [$label, $value, $cls])
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">{{ $label }}</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $cls }}">{{ $value }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Documents</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $documents->count() }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">files on record</p>
                    @if($documents->where('expiry_date', '!=', null)->filter(fn($d) => $d->isExpired())->count() > 0)
                    <p class="text-xs text-red-600 mt-1 font-semibold">
                        {{ $documents->filter(fn($d) => $d->isExpired())->count() }} expired
                    </p>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">Added by</h3>
                    <p class="text-sm text-gray-800">{{ $client->creator?->name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $client->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TAB: PROFILE ─────────────────────────────────────────────────────── --}}
    <div x-show="tab==='profile'" x-cloak>
        <div class="space-y-5">

            @if($client->client_type === 'corporate' && $client->signatories->count())
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-700">Authorised signatories</h3></div>
                <div class="divide-y divide-gray-100">
                    @foreach($client->signatories as $sig)
                    <div class="px-5 py-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div><span class="text-xs text-gray-400 block">Name</span>{{ $sig->full_name }}</div>
                        <div><span class="text-xs text-gray-400 block">Position</span>{{ $sig->position ?? '—' }}</div>
                        <div><span class="text-xs text-gray-400 block">Passport</span>{{ $sig->passport_number ?? '—' }}</div>
                        <div><span class="text-xs text-gray-400 block">Passport expiry</span>
                            @if($sig->passport_expiry)
                                <span class="{{ \Carbon\Carbon::parse($sig->passport_expiry)->isPast() ? 'text-red-600 font-semibold' : '' }}">
                                    {{ \Carbon\Carbon::parse($sig->passport_expiry)->format('d M Y') }}
                                </span>
                            @else —
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($client->client_type === 'corporate' && $client->shareholders->count())
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-700">Shareholders</h3></div>
                <div class="divide-y divide-gray-100">
                    @foreach($client->shareholders as $sh)
                    <div class="px-5 py-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div><span class="text-xs text-gray-400 block">Name</span>{{ $sh->name }}</div>
                        <div><span class="text-xs text-gray-400 block">Type</span>{{ ucfirst($sh->shareholder_type) }}</div>
                        <div><span class="text-xs text-gray-400 block">Ownership</span>{{ $sh->ownership_percentage ? $sh->ownership_percentage.'%' : '—' }}</div>
                        <div><span class="text-xs text-gray-400 block">UBO</span>
                            @if($sh->is_ubo)<span class="text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-medium">Yes</span>@else <span class="text-gray-400">No</span>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($client->ubos->count())
            <div class="bg-white rounded-xl border border-amber-200">
                <div class="px-5 py-4 border-b border-amber-100 bg-amber-50 rounded-t-xl"><h3 class="text-sm font-semibold text-amber-800">Ultimate Beneficial Owners (UBOs)</h3></div>
                <div class="divide-y divide-gray-100">
                    @foreach($client->ubos as $ubo)
                    <div class="px-5 py-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div><span class="text-xs text-gray-400 block">Name</span>{{ $ubo->full_name }}</div>
                        <div><span class="text-xs text-gray-400 block">Nationality</span>{{ $ubo->nationality ?? '—' }}</div>
                        <div><span class="text-xs text-gray-400 block">Ownership</span>{{ $ubo->ownership_percentage ? $ubo->ownership_percentage.'%' : '—' }}</div>
                        <div><span class="text-xs text-gray-400 block">PEP</span>
                            @if($ubo->pep_status)<span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-medium">PEP</span>@else <span class="text-gray-400">No</span>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- AML/CDD --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-700">AML / CDD details</h3></div>
                <div class="p-5 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div><span class="text-xs text-gray-400 block">Source of funds</span>
                        {{ $client->source_of_funds ? implode(', ', array_map(fn($s) => ucfirst(str_replace('_',' ',$s)), $client->source_of_funds)) : '—' }}
                    </div>
                    <div><span class="text-xs text-gray-400 block">Source of wealth</span>
                        {{ $client->source_of_wealth ? implode(', ', array_map(fn($s) => ucfirst(str_replace('_',' ',$s)), $client->source_of_wealth)) : '—' }}
                    </div>
                    <div><span class="text-xs text-gray-400 block">Purpose of relationship</span>{{ $client->purpose_of_relationship ?? '—' }}</div>
                    <div><span class="text-xs text-gray-400 block">Expected monthly volume</span>{{ $client->expected_monthly_volume ? 'AED '.number_format($client->expected_monthly_volume) : '—' }}</div>
                    <div><span class="text-xs text-gray-400 block">Transaction frequency</span>{{ $client->expected_monthly_frequency ?? '—' }}</div>
                    <div><span class="text-xs text-gray-400 block">CDD type</span>{{ $client->cdd_type ? ucfirst($client->cdd_type) : '—' }}</div>
                    <div><span class="text-xs text-gray-400 block">Risk rating</span>
                        @if($client->risk_rating)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $client->riskBadgeColor() }}">{{ ucfirst($client->risk_rating) }}</span>
                        @else —
                        @endif
                    </div>
                    <div><span class="text-xs text-gray-400 block">Next review date</span>
                        <span class="{{ $client->isReviewDue() ? 'text-red-600 font-semibold' : '' }}">
                            {{ $client->next_review_date?->format('d M Y') ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TAB: DOCUMENTS ───────────────────────────────────────────────────── --}}
    <div x-show="tab==='documents'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 mb-5">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Uploaded documents</h3>
                <button type="button" onclick="document.getElementById('upload-modal').classList.remove('hidden')"
                        class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Upload document
                </button>
            </div>

            @if($documents->count())
            <div class="divide-y divide-gray-100">
                @foreach($documents as $doc)
                <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800">{{ $doc->document_label }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $doc->file_name }} · {{ $doc->fileSizeFormatted() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        @if($doc->expiry_date)
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full
                            {{ $doc->isExpired() ? 'bg-red-100 text-red-700' : ($doc->isExpiringSoon() ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500') }}">
                            {{ $doc->isExpired() ? 'Expired' : 'Exp.' }} {{ $doc->expiry_date->format('d M Y') }}
                        </span>
                        @endif
                        <p class="text-xs text-gray-400 hidden md:block">{{ $doc->created_at->format('d M Y') }}</p>
                        <a href="{{ route('tenant.docs.download', [$tenant->slug, $doc->id]) }}"
                           class="text-blue-600 hover:text-blue-700 text-xs font-medium">Download</a>
                        <form method="POST" action="{{ route('tenant.docs.delete', [$tenant->slug, $doc->id]) }}" class="inline"
                              onsubmit="return confirm('Delete this document?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="px-5 py-12 text-center">
                <p class="text-gray-400 text-sm">No documents uploaded yet.</p>
            </div>
            @endif
        </div>

        {{-- Upload modal --}}
        <div id="upload-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">Upload document</h3>
                    <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')"
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('tenant.docs.upload', [$tenant->slug, $client->id]) }}"
                      enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Document type</label>
                        <select name="document_type" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @php $allDocs = $client->client_type === 'corporate' ? \App\Models\ClientDocument::corporateDocTypes() : \App\Models\ClientDocument::individualDocTypes(); @endphp
                            @foreach($allDocs as $d)
                            <option value="{{ $d['type'] }}">{{ $d['label'] }}</option>
                            @endforeach
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Document name / label</label>
                        <input type="text" name="document_label" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">File</label>
                        <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.docx"
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Expiry date (if applicable)</label>
                        <input type="date" name="expiry_date" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">Upload</button>
                        <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')"
                                class="flex-1 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── TAB: SCREENING ───────────────────────────────────────────────────── --}}
    <div x-show="tab==='screening'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700">Screening status</h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        @if($client->screening_date)
                            Last screened: {{ $client->screening_date->format('d M Y, H:i') }}
                            @if($client->screening_reference) · Ref: {{ $client->screening_reference }} @endif
                        @else
                            Not yet screened
                        @endif
                    </p>
                </div>
                <a href="{{ route('tenant.screening', $tenant->slug) }}?client={{ $client->id }}"
                   class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    Run new screening
                </a>
            </div>

            @if($client->screening_result)
            <div class="border border-gray-200 rounded-xl p-4">
                <pre class="text-xs text-gray-600 overflow-x-auto">{{ json_encode($client->screening_result, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @else
            <div class="border-2 border-dashed border-gray-200 rounded-xl p-12 text-center">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <p class="text-gray-400 text-sm">No screening results yet.</p>
                <a href="{{ route('tenant.screening', $tenant->slug) }}?client={{ $client->id }}"
                   class="inline-block mt-3 text-sm text-blue-600 hover:underline font-medium">Run screening now</a>
            </div>
            @endif
        </div>
    </div>

    {{-- ── TAB: RISK ────────────────────────────────────────────────────────── --}}
    <div x-show="tab==='risk'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-semibold text-gray-700">Risk assessment</h3>
                <button class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    Update risk rating
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                @foreach(['Low','Medium','High'] as $r)
                <div class="border rounded-xl p-4 text-center
                    {{ strtolower($r) === $client->risk_rating
                        ? ($r==='Low' ? 'border-green-300 bg-green-50' : ($r==='Medium' ? 'border-amber-300 bg-amber-50' : 'border-red-300 bg-red-50'))
                        : 'border-gray-200' }}">
                    <p class="text-sm font-semibold {{ strtolower($r) === $client->risk_rating ? ($r==='Low' ? 'text-green-700' : ($r==='Medium' ? 'text-amber-700' : 'text-red-700')) : 'text-gray-400' }}">
                        {{ $r }} risk
                        @if(strtolower($r) === $client->risk_rating) ✓ @endif
                    </p>
                </div>
                @endforeach
            </div>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-xs text-gray-400">CDD type</dt><dd class="mt-0.5 font-medium">{{ $client->cdd_type ? ucfirst($client->cdd_type) : '—' }}</dd></div>
                <div><dt class="text-xs text-gray-400">Next review date</dt>
                    <dd class="mt-0.5 font-medium {{ $client->isReviewDue() ? 'text-red-600' : '' }}">
                        {{ $client->next_review_date?->format('d M Y') ?? '—' }}
                    </dd>
                </div>
                @if($client->risk_notes)
                <div class="col-span-2"><dt class="text-xs text-gray-400">Risk notes</dt><dd class="mt-0.5">{{ $client->risk_notes }}</dd></div>
                @endif
            </dl>
        </div>
    </div>

    {{-- ── TAB: DECLARATIONS ────────────────────────────────────────────────── --}}
    <div x-show="tab==='declarations'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-700">Declaration status</h3></div>
            <div class="divide-y divide-gray-100">
                @foreach([
                    ['decl_pep',           'Declaration 1 — PEP',                 'Politically Exposed Person declaration'],
                    ['decl_supply_chain',  'Declaration 2 — Gold supply chain',   'Supply chain sourcing declaration'],
                    ['decl_cahra',         'Declaration 3 — No CAHRA imports',    'Conflict-affected areas declaration'],
                    ['decl_source_of_funds','Declaration 4 — Source of funds',    'Source of funds & wealth declaration'],
                    ['decl_sanctions',     'Declaration 5 — Sanctions',           'Sanctions compliance declaration'],
                    ['decl_ubo',           'Declaration 6 — Beneficial ownership','UBO disclosure declaration'],
                ] as [$field, $title, $desc])
                <div class="px-5 py-3.5 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $title }}</p>
                        <p class="text-xs text-gray-400">{{ $desc }}</p>
                    </div>
                    @if($client->$field)
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-700 bg-green-100 px-2.5 py-1 rounded-full">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Received
                        </span>
                    @else
                        <span class="text-xs font-semibold text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full">Pending</span>
                    @endif
                </div>
                @endforeach

                {{-- Master declaration --}}
                <div class="px-5 py-3.5 flex items-center justify-between bg-gray-50">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Master declaration — physical signature</p>
                        <p class="text-xs text-gray-400">Signed copy must be obtained and uploaded</p>
                    </div>
                    @if($client->decl_master_signed)
                        <span class="text-xs font-semibold text-green-700 bg-green-100 px-2.5 py-1 rounded-full">Signed & uploaded</span>
                    @else
                        <span class="text-xs font-semibold text-red-700 bg-red-100 px-2.5 py-1 rounded-full">Not yet received</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>{{-- end tabs --}}

@endsection
