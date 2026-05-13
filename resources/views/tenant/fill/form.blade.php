<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Form — {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-2xl mx-auto py-10 px-4">

    {{-- Header --}}
    <div class="text-center mb-8">
        @if($tenant->logo_url)
        <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-16 mx-auto mb-4 object-contain">
        @endif
        <h1 class="text-2xl font-bold text-gray-900">{{ $tenant->name }}</h1>
        <p class="text-gray-500 mt-1">KYC / AML Compliance Form</p>
        <p class="text-xs text-gray-400 mt-2">Pursuant to UAE Federal Decree-Law No. 20 of 2018</p>
    </div>

    {{-- Info banner --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-blue-800">Why are we asking for this?</p>
            <p class="text-xs text-blue-700 mt-0.5">Under UAE AML/CFT regulations, we are required to collect and verify identity information from all clients. Your information is stored securely and used solely for compliance purposes.</p>
            <p class="text-xs text-blue-600 mt-1">This link expires on <strong>{{ $fillToken->expires_at->format('d M Y') }}</strong>.</p>
        </div>
    </div>

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ url("/{$tenant->slug}/fill/{$fillToken->token}/submit") }}"
          x-data="{ type: '{{ $fillToken->client_type }}' }">
        @csrf

        @if($fillToken->client_type === 'individual')

        {{-- Individual form --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <h2 class="text-sm font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Personal details</h2>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Full name (as per passport) <span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" value="{{ $fillToken->client_name }}" required
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nationality <span class="text-red-500">*</span></label>
                        <input type="text" name="nationality" required placeholder="e.g. Pakistani, Indian, British"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date of birth <span class="text-red-500">*</span></label>
                        <input type="date" name="dob" required max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Passport number <span class="text-red-500">*</span></label>
                        <input type="text" name="passport_number" required
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Passport expiry date <span class="text-red-500">*</span></label>
                        <input type="date" name="passport_expiry" required
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Emirates ID number</label>
                        <input type="text" name="eid_number" placeholder="784-XXXX-XXXXXXX-X"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Emirates ID expiry</label>
                        <input type="date" name="eid_expiry"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone number <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" required placeholder="+971 50 XXX XXXX"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email address</label>
                        <input type="email" name="email" value="{{ $fillToken->client_email }}"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Occupation / profession</label>
                        <input type="text" name="occupation"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Employer / business name</label>
                        <input type="text" name="employer_name"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <h2 class="text-sm font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Source of funds</h2>
            <p class="text-xs text-gray-500 mb-3">Please select all that apply <span class="text-red-500">*</span></p>
            <div class="grid grid-cols-2 gap-2">
                @foreach(['salary'=>'Salary / employment','business'=>'Business income','savings'=>'Personal savings','investment'=>'Investment returns','property_sale'=>'Property sale','inheritance'=>'Inheritance','other'=>'Other'] as $v=>$l)
                <label class="flex items-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 text-sm">
                    <input type="checkbox" name="source_of_funds[]" value="{{ $v }}" class="rounded border-gray-300 text-blue-600">
                    {{ $l }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <h2 class="text-sm font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Purpose of this transaction</h2>
            <input type="text" name="purpose_of_relationship" required
                   placeholder="e.g. Purchase of gold, exchange of currency, investment..."
                   class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        @else

        {{-- Corporate form --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <h2 class="text-sm font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Company details</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Company name <span class="text-red-500">*</span></label>
                    <input type="text" name="company_name" value="{{ $fillToken->client_name }}" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Trade licence number <span class="text-red-500">*</span></label>
                    <input type="text" name="trade_license_no" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Licence expiry date <span class="text-red-500">*</span></label>
                    <input type="date" name="trade_license_expiry" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Legal form</label>
                    <select name="legal_form" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">— Select —</option>
                        @foreach(['LLC'=>'LLC','Sole Establishment'=>'Sole Establishment','Free Zone LLC'=>'Free Zone LLC','Free Zone Establishment'=>'Free Zone Establishment','Branch'=>'Branch of Foreign Company','Other'=>'Other'] as $v=>$l)
                        <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Country of incorporation</label>
                    <input type="text" name="country_of_incorporation" placeholder="e.g. AE, GB, IN"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Business activity <span class="text-red-500">*</span></label>
                    <input type="text" name="business_activity" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone number <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" required
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email address</label>
                    <input type="email" name="email" value="{{ $fillToken->client_email }}"
                           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Registered address</label>
                    <textarea name="registered_address" rows="2"
                              class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
            <h2 class="text-sm font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">Source of funds & purpose</h2>
            <div class="grid grid-cols-2 gap-2 mb-4">
                @foreach(['trading_revenue'=>'Trading revenues','business_income'=>'Business income','investment'=>'Investment returns','other'=>'Other'] as $v=>$l)
                <label class="flex items-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 text-sm">
                    <input type="checkbox" name="source_of_funds[]" value="{{ $v }}" class="rounded border-gray-300 text-blue-600">
                    {{ $l }}
                </label>
                @endforeach
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Purpose of business relationship <span class="text-red-500">*</span></label>
                <input type="text" name="purpose_of_relationship" required
                       placeholder="e.g. Gold trading, bullion transactions..."
                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        @endif

        {{-- Declaration --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Declaration</h2>
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" required class="rounded border-gray-300 text-blue-600 mt-0.5 w-5 h-5 flex-shrink-0">
                <p class="text-sm text-gray-600">I confirm that all information provided in this form is true, accurate and complete to the best of my knowledge. I understand that providing false information may result in legal consequences under UAE law.</p>
            </label>
        </div>

        <button type="submit"
                class="w-full py-3.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition">
            Submit KYC Form
        </button>

        <p class="text-center text-xs text-gray-400 mt-4">Your information is encrypted and transmitted securely. This form is powered by Blue Arrow Management Consultants.</p>
    </form>
</div>
</body>
</html>
