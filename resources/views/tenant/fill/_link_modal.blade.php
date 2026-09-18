{{-- Generated/reissued KYC link modal -- shared by clients/index.blade.php
     and fill/pending.blade.php, both of which flash the same session keys. --}}
@if(session('fill_link'))
<div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" x-data="{ show: true }" x-show="show">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-sm font-bold text-gray-800 mb-2">KYC link generated ✓</h3>
        @if(session('email_sent'))
        <p class="text-xs text-green-600 mb-3">✓ Email sent to client</p>
        @elseif(session('email_attempted'))
        <div class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2 mb-3">
            <p class="font-semibold">✗ Could not email {{ session('fill_client_email') }}</p>
            <p class="text-red-500 mt-0.5">The link below still works — copy and share it manually.</p>
        </div>
        @endif
        <p class="text-xs text-gray-500 mb-2">Copy and share this link with your client:</p>
        <div class="flex gap-2 mb-2">
            <input type="text" value="{{ session('fill_link') }}" readonly id="fill-link-input"
                   class="flex-1 px-3 py-2 text-xs border border-gray-200 rounded-lg bg-gray-50 font-mono">
            <button onclick="navigator.clipboard.writeText(document.getElementById('fill-link-input').value).then(()=>this.textContent='Copied!')"
                    class="px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 whitespace-nowrap">
                Copy
            </button>
        </div>
        <p class="text-xs text-gray-400">Expires in 7 days · One-time use only</p>
        <button @click="show=false" class="mt-4 w-full py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Close</button>
    </div>
</div>
@endif
