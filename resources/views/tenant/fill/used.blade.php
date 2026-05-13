<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Already Submitted — {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="max-w-md mx-auto px-4 text-center">
    <div class="bg-white rounded-2xl border border-gray-200 p-8">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-2">Already submitted</h1>
        <p class="text-gray-500 text-sm">This KYC form has already been completed. If you believe this is an error, please contact <strong>{{ $tenant->name }}</strong>.</p>
        <p class="text-xs text-gray-400 mt-6">{{ $tenant->name }} · AML Compliance Department</p>
    </div>
</div>
</body>
</html>
