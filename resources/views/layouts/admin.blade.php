<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BlueArrow Portal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { 50:'#eff6ff', 500:'#1a56db', 600:'#1e40af', 700:'#1e3a8a' } } } }
        }
    </script>
</head>
<body class="h-full flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-brand-700 text-white flex flex-col flex-shrink-0">

        <!-- Logo -->
        <div class="px-6 py-5 border-b border-blue-600">
            <span class="text-xl font-bold tracking-tight">BlueArrow</span>
            <span class="text-xs text-blue-300 block">Internal Portal</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center px-3 py-2 rounded-md text-sm font-medium
                      {{ request()->routeIs('admin.dashboard') || request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-blue-600' }}">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <!-- CRM & Sales -->
            <div class="pt-3">
                <p class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider">CRM & Sales</p>

                <a href="{{ route('crm.index') }}"
                   class="mt-1 flex items-center px-3 py-2 rounded-md text-sm font-medium
                          {{ request()->routeIs('crm.*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-blue-600' }}">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Clients
                </a>

                <a href="{{ route('quotations.index') }}"
                   class="mt-1 flex items-center px-3 py-2 rounded-md text-sm font-medium
                          {{ request()->routeIs('quotations.*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-blue-600' }}">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Quotations
                </a>
            </div>

            <!-- Compliance -->
            <div class="pt-3">
                <p class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider">Compliance</p>

                <a href="{{ route('kyc.index') }}"
                   class="mt-1 flex items-center px-3 py-2 rounded-md text-sm font-medium
                          {{ request()->routeIs('kyc.*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-blue-600' }}">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    KYC Submissions
                </a>

                <a href="{{ route('kyc.tenants') }}"
                   class="mt-1 flex items-center px-3 py-2 rounded-md text-sm font-medium text-blue-100 hover:bg-blue-600">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Tenant Portals
                </a>

                <a href="{{ route('screening.index') }}"
                   class="mt-1 flex items-center px-3 py-2 rounded-md text-sm font-medium text-blue-100 hover:bg-blue-600">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Screening
                </a>
            </div>

            <!-- Tools -->
            <div class="pt-3">
                <p class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider">Tools</p>

                <a href="{{ route('marketing.index') }}"
                   class="mt-1 flex items-center px-3 py-2 rounded-md text-sm font-medium text-blue-100 hover:bg-blue-600">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    Marketing
                </a>

                <a href="{{ route('whatsapp.index') }}"
                   class="mt-1 flex items-center px-3 py-2 rounded-md text-sm font-medium text-blue-100 hover:bg-blue-600">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    WhatsApp
                </a>

                <a href="{{ route('accounting.launch') }}" target="_blank"
                   class="mt-1 flex items-center px-3 py-2 rounded-md text-sm font-medium text-blue-100 hover:bg-blue-600">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Accounting
                    <svg class="ml-auto h-3 w-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>

            <!-- Bottom divider + Settings -->
            <div class="pt-3 border-t border-blue-600 mt-2">
                <a href="{{ route('settings.index') }}"
                   class="mt-1 flex items-center px-3 py-2 rounded-md text-sm font-medium
                          {{ request()->routeIs('settings.*') ? 'bg-blue-600 text-white' : 'text-blue-100 hover:bg-blue-600' }}">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>
            </div>

        </nav>

        <!-- User info at bottom -->
        <div class="border-t border-blue-600 p-4">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-sm font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-blue-300">{{ ucfirst(str_replace('_',' ',auth()->user()->role)) }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                    @csrf
                    <button type="submit" class="text-blue-300 hover:text-white text-xs">Logout</button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main content area -->
    <div class="flex-1 flex flex-col min-h-0 overflow-hidden">

        <!-- Top bar -->
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between flex-shrink-0">
            <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
            <div class="text-sm text-gray-500">{{ now()->format('l, d M Y') }}</div>
        </header>

        <!-- Page content -->
        <main class="flex-1 overflow-y-auto p-6">
            @if(session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>

{{-- ── AI QUICK TASK CAPTURE ─────────────────────────────────────────────── --}}
<div x-data="quickTask()" x-cloak>

    {{-- Floating button --}}
    <button @click="open = true" title="Add task by voice or text"
            class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-xl flex items-center justify-center transition-all hover:scale-110">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
        </svg>
    </button>

    {{-- Modal backdrop --}}
    <div x-show="open" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/50"
         @click.self="reset()">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" @click.stop>

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800">Quick task</p>
                        <p class="text-xs text-gray-400">Speak or type — AI will figure out the rest</p>
                    </div>
                </div>
                <button @click="reset()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-4">

                {{-- Input + mic --}}
                <div x-show="step === 'input'">
                    <div class="flex gap-2">
                        <input type="text" x-model="text" @keydown.enter="parse()"
                               placeholder="e.g. Call Prince Jewellers about SLA renewal tomorrow"
                               class="flex-1 px-4 py-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"
                               x-ref="textInput">

                        {{-- Mic button --}}
                        <button type="button" @click="toggleMic()"
                                :class="listening ? 'bg-red-500 hover:bg-red-600 animate-pulse' : 'bg-gray-100 hover:bg-gray-200'"
                                class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 transition"
                                :title="listening ? 'Listening… click to stop' : 'Click to speak'">
                            <svg class="w-5 h-5" :class="listening ? 'text-white' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                        </button>
                    </div>

                    <p x-show="listening" class="text-xs text-red-500 mt-1 animate-pulse">● Listening… speak now</p>
                    <p x-show="!listening && !text" class="text-xs text-gray-400 mt-1">Try: "Follow up with Ahmed Al Rashidi next Monday" or "Send quotation to Prince Jewellers urgent"</p>

                    <button @click="parse()" :disabled="!text.trim() || parsing"
                            class="mt-3 w-full py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-40 transition flex items-center justify-center gap-2">
                        <svg x-show="parsing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span x-text="parsing ? 'Understanding…' : 'Parse task'"></span>
                    </button>

                    <p x-show="error" class="text-xs text-red-600 mt-2" x-text="error"></p>
                </div>

                {{-- Confirmation card --}}
                <div x-show="step === 'confirm'" class="space-y-4">

                    {{-- Client --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">CLIENT</label>
                        <template x-if="parsed.matched_client">
                            <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span class="text-sm font-semibold text-gray-800" x-text="parsed.matched_client.display"></span>
                                </div>
                                <button @click="parsed.matched_client = null; parsed.client_suggestions = []" class="text-xs text-gray-400 hover:text-gray-600">Change</button>
                            </div>
                        </template>
                        <template x-if="!parsed.matched_client">
                            <div class="space-y-2">
                                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                                    <span x-show="parsed.client_name">Couldn't find "<span x-text="parsed.client_name"></span>" — </span>pick from list or type below:
                                </div>
                                <template x-if="parsed.client_suggestions && parsed.client_suggestions.length">
                                    <div class="space-y-1">
                                        <template x-for="s in parsed.client_suggestions" :key="s.id">
                                            <button @click="parsed.matched_client = s; selectedClientId = s.id"
                                                    class="w-full text-left px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition"
                                                    x-text="s.display"></button>
                                        </template>
                                    </div>
                                </template>
                                <input type="text" placeholder="Search client name…" @input.debounce.300ms="searchClients($event.target.value)"
                                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div x-show="clientResults.length" class="space-y-1">
                                    <template x-for="c in clientResults" :key="c.id">
                                        <button @click="parsed.matched_client = c; parsed.client_suggestions = []; clientResults = []"
                                                class="w-full text-left px-3 py-2 text-sm bg-white border border-gray-200 rounded-lg hover:bg-blue-50 transition"
                                                x-text="c.display"></button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Task description --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">TASK</label>
                        <input type="text" x-model="parsed.task"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Due date + Priority --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">DUE DATE</label>
                            <input type="date" x-model="parsed.due_date"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">PRIORITY</label>
                            <select x-model="parsed.priority"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button @click="save()" :disabled="!parsed.matched_client || !parsed.task || saving"
                                class="flex-1 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 disabled:opacity-40 transition flex items-center justify-center gap-2">
                            <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            <span x-text="saving ? 'Saving…' : 'Add task'"></span>
                        </button>
                        <button @click="step = 'input'" class="px-4 py-2.5 text-sm text-gray-500 bg-gray-100 rounded-xl hover:bg-gray-200 transition">Back</button>
                    </div>
                </div>

                {{-- Success state --}}
                <div x-show="step === 'done'" class="text-center py-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-sm font-bold text-gray-800">Task added!</p>
                    <p class="text-xs text-gray-500 mt-1" x-text="'Added to ' + savedClient"></p>
                    <div class="flex gap-2 mt-4">
                        <a :href="savedUrl" class="flex-1 text-center py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-xl hover:bg-blue-50 transition">View client →</a>
                        <button @click="resetForNew()" class="flex-1 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition">Add another</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function quickTask() {
    return {
        open:          false,
        step:          'input',   // input | confirm | done
        text:          '',
        listening:     false,
        parsing:       false,
        saving:        false,
        error:         '',
        parsed:        {},
        clientResults: [],
        savedClient:   '',
        savedUrl:      '',
        recognition:   null,

        parseUrl:      '{{ route('tasks.quick-capture') }}',
        saveUrl:       '{{ route('tasks.quick-capture.save') }}',
        clientSearch:  '{{ route('crm.index') }}',
        csrf:          document.querySelector('meta[name="csrf-token"]')?.content,

        init() {
            // Pre-focus text input when modal opens
            this.$watch('open', v => { if (v) this.$nextTick(() => this.$refs.textInput?.focus()); });
        },

        toggleMic() {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SR) { alert('Voice input is not supported in this browser. Please use Chrome or Edge.'); return; }

            if (this.listening) {
                this.recognition?.stop();
                return;
            }

            this.recognition = new SR();
            this.recognition.lang = 'en-US';
            this.recognition.continuous = false;
            this.recognition.interimResults = false;

            this.recognition.onstart  = () => { this.listening = true; };
            this.recognition.onend    = () => { this.listening = false; };
            this.recognition.onerror  = () => { this.listening = false; };
            this.recognition.onresult = (e) => {
                this.text = e.results[0][0].transcript;
                this.listening = false;
                // Auto-parse after voice input
                this.$nextTick(() => this.parse());
            };

            this.recognition.start();
        },

        async parse() {
            if (!this.text.trim()) return;
            this.parsing = true;
            this.error   = '';
            try {
                const res  = await fetch(this.parseUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body: JSON.stringify({ text: this.text }),
                });
                const data = await res.json();
                if (!res.ok) { this.error = data.error || 'Failed to parse.'; return; }
                this.parsed = data;
                this.step   = 'confirm';
            } catch(e) {
                this.error = 'Network error. Please try again.';
            } finally {
                this.parsing = false;
            }
        },

        async searchClients(q) {
            if (q.length < 2) { this.clientResults = []; return; }
            try {
                const res  = await fetch('{{ route('crm.search') }}?q=' + encodeURIComponent(q), {
                    headers: { 'X-CSRF-TOKEN': this.csrf }
                });
                if (res.ok) this.clientResults = await res.json();
            } catch(e) { this.clientResults = []; }
        },

        async save() {
            if (!this.parsed.matched_client || !this.parsed.task) return;
            this.saving = true;
            try {
                const res  = await fetch(this.saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body: JSON.stringify({
                        crm_client_id:    this.parsed.matched_client.id,
                        task_description: this.parsed.task,
                        due_date:         this.parsed.due_date || null,
                        priority:         this.parsed.priority || 'medium',
                    }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) { this.error = data.error || 'Failed to save.'; this.step = 'confirm'; return; }
                this.savedClient = data.client_name;
                this.savedUrl    = data.profile_url;
                this.step        = 'done';
            } catch(e) {
                this.error = 'Network error.';
            } finally {
                this.saving = false;
            }
        },

        reset() {
            this.open = false;
            setTimeout(() => {
                this.step = 'input'; this.text = ''; this.error = '';
                this.parsed = {}; this.clientResults = []; this.saving = false; this.parsing = false;
            }, 300);
        },

        resetForNew() {
            this.step = 'input'; this.text = ''; this.error = '';
            this.parsed = {}; this.clientResults = [];
            this.$nextTick(() => this.$refs.textInput?.focus());
        },
    };
}
</script>

</body>
</html>
