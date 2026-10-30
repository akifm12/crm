@php
    $planStyles = [
        'TRIAL' => 'bg-amber-50 text-amber-700 border-amber-200', 'ESSENTIAL' => 'bg-sky-50 text-sky-700 border-sky-200',
        'COMPLETE' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'API' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'PREMIUM' => 'bg-purple-50 text-purple-700 border-purple-200',
    ];
    $planNames = ['TRIAL' => 'Trial', 'ESSENTIAL' => 'Essential', 'COMPLETE' => 'Complete', 'API' => 'Corporate API',
                  'PREMIUM' => 'Premium data', 'PORTAL' => 'Portal tenant', 'STANDARD' => 'Standard (legacy)', 'INTERNAL' => 'Internal'];
@endphp
<span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full border {{ $planStyles[$plan] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">{{ $planNames[$plan] ?? ($plan ?: '—') }}</span>
