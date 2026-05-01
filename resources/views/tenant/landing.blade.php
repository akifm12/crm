{{-- resources/views/tenant/landing.blade.php --}}
{{-- $tenant is available globally via ResolveTenant middleware --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} — Client Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --brand: {{ $tenant->primary_color }}; }
        .btn-brand { background-color: {{ $tenant->primary_color }}; }
        .btn-brand:hover { opacity: 0.9; }
        .border-brand { border-color: {{ $tenant->primary_color }}; }
        .text-brand { color: {{ $tenant->primary_color }}; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
            @if($tenant->logo_url)
                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-10 object-contain">
            @else
                <span class="text-xl font-bold text-brand">{{ $tenant->name }}</span>
            @endif
            <a href="{{ route('tenant.login', $tenant->slug) }}"
               class="text-sm font-medium text-brand border border-brand rounded-lg px-4 py-2 hover:bg-gray-50 transition">
                Client login
            </a>
        </div>
    </header>

    <!-- Hero -->
    <main class="flex-1 flex items-center justify-center px-6">
        <div class="max-w-lg w-full text-center py-16">

            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                Welcome to {{ $tenant->name }}
            </h1>
            <p class="text-gray-500 mb-8">
                Complete your onboarding and KYC verification securely online.
                It takes less than 10 minutes.
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('tenant.apply', $tenant->slug) }}"
                   class="btn-brand text-white font-semibold px-6 py-3 rounded-xl transition text-sm">
                    Start application
                </a>
                <a href="{{ route('tenant.login', $tenant->slug) }}"
                   class="bg-white border border-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-xl hover:bg-gray-50 transition text-sm">
                    Check my status
                </a>
            </div>

            <p class="mt-8 text-xs text-gray-400">
                Powered by <a href="https://bluearrow.ae" class="underline">BlueArrow</a>
            </p>
        </div>
    </main>

</body>
</html>
