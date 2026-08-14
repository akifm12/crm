@extends('layouts.tenant')

@section('title', 'Review Screening Hits — ' . $log->query)

@section('content')
@php
    $hits  = $log->hitsNeedingReview();
    $total = count($hits);
    $done  = collect($log->reviews ?? [])->count();
    $ref   = $log->reference ?? 'SCR-' . strtoupper(substr(md5($log->id), 0, 8));
    $saveUrl = route('tenant.screening.log.review.save', [$tenant->slug, $log->id]);
    $pdfUrl  = route('tenant.screening.log.pdf', [$tenant->slug, $log->id]);
@endphp

<div class="max-w-4xl mx-auto px-4 py-6"
     x-data="{ reviewed: {{ $done }}, total: {{ $total }} }"
     @hit-saved.window="reviewed = $event.detail.reviewed">

    {{-- Page header --}}
    <div class="mb-6">
        <a href="{{ route('tenant.screening', $tenant->slug) }}"
           class="text-sm text-blue-600 hover:underline">← Back to Screening</a>
        <h1 class="text-xl font-bold text-gray-900 mt-2">EDD Hit Review</h1>
        <p class="text-sm text-gray-500">Reference: <span class="font-mono">{{ $ref }}</span> · Subject: <strong>{{ $log->query }}</strong></p>
    </div>

    {{-- Progress bar --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Review progress</span>
            <span class="text-sm font-semibold"
                  :class="reviewed >= total ? 'text-green-600' : 'text-amber-600'">
                <span x-text="reviewed"></span> / {{ $total }} reviewed
            </span>
        </div>
        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500"
                 :class="reviewed >= total ? 'bg-green-500' : 'bg-amber-500'"
                 :style="'width:' + Math.round((reviewed / total) * 100) + '%'"></div>
        </div>
        <p class="text-xs text-gray-400 mt-1.5">Mark each hit as False Positive or True Positive to proceed.</p>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Hit cards --}}
    <div class="space-y-4">
    @foreach($hits as $hit)
    @php
        $hitId    = $hit['id'];
        $existing = $log->reviewFor($hitId);
        $verdict  = $existing['verdict'] ?? null;
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden"
         x-data="{
             hitId: '{{ $hitId }}',
             verdict: '{{ $verdict ?? '' }}',
             notes: {{ json_encode($existing['notes'] ?? '') }},
             saving: false,
             saved: {{ $verdict ? 'true' : 'false' }},
             savedVerdict: '{{ $verdict ?? '' }}',
             errorMsg: '',
             saveUrl: '{{ $saveUrl }}',
             async save() {
                 if (!this.verdict || this.saving) return;
                 this.saving = true;
                 this.errorMsg = '';
                 try {
                     const res = await fetch(this.saveUrl, {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                             'Accept': 'application/json',
                         },
                         body: JSON.stringify({ hit_id: this.hitId, verdict: this.verdict, notes: this.notes }),
                     });
                     const data = await res.json();
                     if (data.success) {
                         this.savedVerdict = this.verdict;
                         this.saved = true;
                         this.$dispatch('hit-saved', { reviewed: data.reviewed });
                     } else {
                         this.errorMsg = 'Save failed. Please try again.';
                     }
                 } catch (e) {
                     this.errorMsg = 'Network error. Please try again.';
                 } finally {
                     this.saving = false;
                 }
             }
         }">

        {{-- Hit header --}}
        <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="font-semibold text-gray-900">{{ $hit['name'] ?? 'Unknown' }}</p>
                <div class="flex flex-wrap gap-1.5 mt-1.5">
                    @if(!empty($hit['type']))
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">{{ $hit['type'] }}</span>
                    @endif
                    @if(!empty($hit['riskLevel']))
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $hit['riskLevel']==='CRITICAL' ? 'bg-red-100 text-red-700' :
                           ($hit['riskLevel']==='HIGH' ? 'bg-orange-100 text-orange-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ $hit['riskLevel'] }}
                    </span>
                    @endif
                    @if(!empty($hit['list']['name']))
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $hit['list']['name'] }}</span>
                    @endif
                    @if(!empty($hit['matchScore']))
                    <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">{{ $hit['matchScore'] }}% match</span>
                    @endif
                    @if(!empty($hit['matchType']))
                    <span class="text-xs bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full">{{ ucfirst($hit['matchType']) }}</span>
                    @endif
                </div>
                @if(!empty($hit['programs']) && is_array($hit['programs']))
                <p class="text-xs text-gray-500 mt-1">{{ implode(', ', $hit['programs']) }}</p>
                @endif
                @if(!empty($hit['reason']))
                <p class="text-xs text-gray-400 mt-0.5">{{ $hit['reason'] }}</p>
                @endif
            </div>
            {{-- Saved verdict badge --}}
            <div x-show="saved" x-cloak
                 class="flex-shrink-0 flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                 :class="savedVerdict === 'false_positive' ? 'bg-gray-100 text-gray-600' : 'bg-red-100 text-red-700'">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="savedVerdict === 'false_positive' ? 'False Positive' : 'True Positive'"></span>
            </div>
        </div>

        {{-- Review controls --}}
        <div class="px-5 py-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Disposition</p>
            <div class="flex gap-3 mb-4">
                <button type="button"
                    @click="verdict = 'false_positive'"
                    :class="verdict === 'false_positive'
                        ? 'bg-gray-700 text-white border-gray-700'
                        : 'bg-white text-gray-600 border-gray-300 hover:border-gray-500'"
                    class="flex-1 px-4 py-2.5 text-sm font-medium border rounded-lg transition">
                    False Positive
                </button>
                <button type="button"
                    @click="verdict = 'true_positive'"
                    :class="verdict === 'true_positive'
                        ? 'bg-red-600 text-white border-red-600'
                        : 'bg-white text-red-600 border-red-300 hover:border-red-500'"
                    class="flex-1 px-4 py-2.5 text-sm font-medium border rounded-lg transition">
                    True Positive
                </button>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-500 mb-1">
                    Reviewer notes <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <textarea x-model="notes" rows="2"
                    placeholder="Explain the basis for your determination…"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>

            <div class="flex items-center gap-3">
                <button type="button"
                    :disabled="!verdict || saving"
                    @click="save()"
                    :class="!verdict || saving ? 'opacity-40 cursor-not-allowed' : 'hover:bg-blue-700'"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg transition">
                    <span x-show="!saving">Save determination</span>
                    <span x-show="saving" x-cloak>Saving…</span>
                </button>
                <span x-show="saved && !saving" x-cloak class="text-xs text-green-600 font-medium">Saved</span>
                <span x-show="errorMsg" x-cloak class="text-xs text-red-500" x-text="errorMsg"></span>
            </div>
        </div>

    </div>
    @endforeach
    </div>

    {{-- Finalise section --}}
    <div class="mt-6 pt-5 border-t border-gray-200">
        <div x-show="reviewed < total">
            <p class="text-sm text-gray-500">
                Review all <strong>{{ $total }}</strong> hit(s) above before generating the EDD report.
            </p>
        </div>
        <div x-show="reviewed >= total" x-cloak>
            <p class="text-sm text-green-700 font-medium mb-3">All hits reviewed. You can now generate the EDD report.</p>
            <a href="{{ $pdfUrl }}" target="_blank"
               class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Generate EDD Report (PDF)
            </a>
        </div>
    </div>

</div>
@endsection
