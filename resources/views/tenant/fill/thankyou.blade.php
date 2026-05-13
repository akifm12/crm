<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submitted — {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="max-w-md mx-auto px-4 text-center">
    <div class="bg-white rounded-2xl border border-gray-200 p-8">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-2">Thank you!</h1>
        <p class="text-gray-500 text-sm mb-6">Your KYC information has been submitted to <strong>{{ $tenant->name }}</strong>. Our compliance team will review your submission and contact you if any additional information is required.</p>
        <div class="bg-blue-50 rounded-xl p-4 text-left">
            <p class="text-xs font-semibold text-blue-800">What happens next?</p>
            <ul class="text-xs text-blue-700 mt-2 space-y-1">
                <li>✓ Your information has been received securely</li>
                <li>✓ Our compliance team will verify your details</li>
                <li>✓ You may be contacted if additional documents are needed</li>
            </ul>
        </div>
        <p class="text-xs text-gray-400 mt-6">{{ $tenant->name }} · AML Compliance Department</p>
    </div>
</div>
</body>
</html>
