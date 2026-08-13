<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $training->employee_name }} — Certificate</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">

        {{-- Logo --}}
        <div class="text-center mb-6">
            @php $logoPath = public_path('images/logo.png'); @endphp
            @if(file_exists($logoPath))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}"
                     class="h-10 mx-auto mb-3" alt="Blue Arrow">
            @endif
            <p class="text-xs text-gray-400">Blue Arrow Management Consultants</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

            <div class="bg-gradient-to-r from-[#1b2d4f] to-[#2a4070] px-6 py-5">
                <p class="text-xs font-semibold text-amber-300 uppercase tracking-widest mb-1">Certificate of Professional Training</p>
                <h1 class="text-lg font-bold text-white">{{ $training->employee_name }}</h1>
                @if($training->employee_role)
                    <p class="text-sm text-blue-200 mt-0.5">{{ $training->employee_role }}</p>
                @endif
                <p class="text-sm text-blue-300 mt-0.5">{{ $training->client->company_name }}</p>
            </div>

            <div class="px-6 py-5 space-y-4">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Training Programme</p>
                    <p class="text-sm font-semibold text-gray-800 mt-0.5">{{ $training->training_type }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Training Date</p>
                        <p class="text-sm text-gray-800 mt-0.5">{{ $training->training_date->format('d F Y') }}</p>
                    </div>
                    @if($training->expiry_date)
                    <div>
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Valid Until</p>
                        <p class="text-sm mt-0.5 {{ $training->expiry_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-800' }}">
                            {{ $training->expiry_date->format('d F Y') }}
                            @if($training->expiry_date->isPast())
                                <span class="block text-xs text-red-500">(Expired)</span>
                            @endif
                        </p>
                    </div>
                    @endif
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Certificate No.</p>
                    <p class="text-sm font-bold text-gray-800 mt-0.5">BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>

            <div class="px-6 pb-6">
                <a href="{{ route('certificate.public.download', $token) }}"
                   class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-white bg-[#1b2d4f] rounded-xl hover:bg-[#2a4070] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Certificate (PDF)
                </a>
            </div>

            <div class="bg-gray-50 border-t border-gray-100 px-6 py-3">
                <p class="text-xs text-gray-400 text-center leading-relaxed">
                    Issued by Blue Arrow Management Consultants · Licence No. 3003-02 · SRTIP
                </p>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">
            <a href="{{ route('certificate.verify', $training->id) }}" class="hover:text-gray-600">Verify this certificate</a>
        </p>

    </div>
</body>
</html>
