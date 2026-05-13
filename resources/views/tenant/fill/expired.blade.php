<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Expired — {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="max-w-md mx-auto px-4 text-center">
    <div class="bg-white rounded-2xl border border-gray-200 p-8">
        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-2">This link has expired</h1>
        <p class="text-gray-500 text-sm">The KYC form link you used has expired. Please contact <strong>{{ $tenant->name }}</strong> to request a new link.</p>
        <p class="text-xs text-gray-400 mt-6">{{ $tenant->name }} · AML Compliance Department</p>
    </div>
</div>
</body>
</html>
