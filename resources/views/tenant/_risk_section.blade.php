{{-- resources/views/tenant/_risk_section.blade.php --}}
@php
$colors = [
    'blue'   => ['bg-blue-50 border-blue-200',   'text-blue-700',   'bg-blue-600'],
    'purple' => ['bg-purple-50 border-purple-200','text-purple-700', 'bg-purple-600'],
    'amber'  => ['bg-amber-50 border-amber-200',  'text-amber-700',  'bg-amber-600'],
    'orange' => ['bg-orange-50 border-orange-200','text-orange-700', 'bg-orange-600'],
    'teal'   => ['bg-teal-50 border-teal-200',    'text-teal-700',   'bg-teal-600'],
    'red'    => ['bg-red-50 border-red-200',       'text-red-700',    'bg-red-600'],
];
[$sectionBg, $sectionText, $activeBg] = $colors[$color ?? 'blue'];
@endphp

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 {{ $sectionBg }} border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-semibold {{ $sectionText }}">{{ $title }}</h3>
        </div>
        <span class="text-xs font-mono bg-white/60 px-2 py-0.5 rounded-full {{ $sectionText }} font-semibold">Weight: {{ $weight }}</span>
    </div>

    <div class="divide-y divide-gray-50">
        @foreach($factors as [$key, $label, $options, $default])
        <div class="px-5 py-4">
            <p class="text-sm font-medium text-gray-700 mb-3">{{ $label }}</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2" x-data="{ val: {{ $saved($key, $default) }} }">
                @foreach($options as $score => $desc)
                @php
                    $isSelected = $saved($key, $default) == $score;
                    $scoreLabel = $score == 1 ? 'Low' : ($score == 2 ? 'Medium' : 'High');
                    $scoreCls   = $score == 1 ? 'text-green-700' : ($score == 2 ? 'text-amber-700' : 'text-red-700');
                @endphp
                <label class="relative flex items-start gap-3 p-3 border rounded-xl cursor-pointer transition-all"
                       :class="val === {{ $score }} ? '{{ $activeBg }} border-transparent text-white' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'">
                    <input type="radio"
                           name="factors[{{ $key }}]"
                           value="{{ $score }}"
                           {{ $isSelected ? 'checked' : '' }}
                           @change="val = {{ $score }}"
                           class="sr-only">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center mt-0.5 transition-all"
                         :class="val === {{ $score }} ? 'border-white bg-white/20' : 'border-gray-300'">
                        <span class="text-xs font-bold" :class="val === {{ $score }} ? 'text-white' : 'text-gray-400'">{{ $score }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold mb-0.5 {{ $scoreCls }}"
                           :class="val === {{ $score }} ? '!text-white' : ''">
                            {{ $scoreLabel }}
                        </p>
                        <p class="text-xs leading-relaxed text-gray-500"
                           :class="val === {{ $score }} ? '!text-white/80' : ''">
                            {{ $desc }}
                        </p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
