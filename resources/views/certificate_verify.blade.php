<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification — Blue Arrow</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full">

        {{-- Header --}}
        <div class="text-center mb-6">
            @php $logoPath = public_path('images/logo.png'); @endphp
            @if(file_exists($logoPath))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}"
                     class="h-12 mx-auto mb-3" alt="Blue Arrow">
            @endif
            <h1 class="text-lg font-bold text-gray-800">Certificate Verification</h1>
            <p class="text-xs text-gray-500 mt-1">Blue Arrow Management Consultants</p>
        </div>

        {{-- Verified badge --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-green-50 border-b border-green-100 px-6 py-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-green-800">Certificate Verified</p>
                    <p class="text-xs text-green-600">This certificate is authentic and issued by Blue Arrow Management Consultants</p>
                </div>
            </div>

            <div class="px-6 py-5 space-y-4">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Certificate No.</p>
                    <p class="text-sm font-bold text-gray-800 mt-0.5">BA-TRN-{{ str_pad($training->id, 5, '0', STR_PAD_LEFT) }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Recipient</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">{{ $training->employee_name }}</p>
                    @if($training->employee_role)
                        <p class="text-xs text-gray-500">{{ $training->employee_role }}</p>
                    @endif
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Organisation</p>
                    <p class="text-sm text-gray-800 mt-0.5">{{ $training->client->company_name }}</p>
                </div>

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
                                <span class="text-xs">(Expired)</span>
                            @endif
                        </p>
                    </div>
                    @endif
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Conducted By</p>
                    <p class="text-sm text-gray-800 mt-0.5">Blue Arrow Management Consultants</p>
                </div>
            </div>

            <div class="bg-gray-50 border-t border-gray-100 px-6 py-3">
                <p class="text-xs text-gray-400 text-center leading-relaxed">
                    Blue Arrow Management Consultants is authorised to provide Professional and Management
                    Development Training under Licence No. 3003-02 issued by SRTIP.
                </p>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">
            Verified on {{ now()->format('d F Y') }} &middot;
            <a href="https://bluearrow.ae" class="hover:text-gray-600">bluearrow.ae</a>
        </p>

    </div>
</body>
</html>
