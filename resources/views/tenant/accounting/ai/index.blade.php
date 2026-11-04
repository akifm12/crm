@extends('layouts.tenant')
@section('title', 'AI Assistant — ' . $tenant->name)
@section('page-title', 'AI Assistant')
@section('page-subtitle', 'Ask questions about your accounts')

@section('content')
<div class="max-w-2xl" x-data="aiChat()">
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 110 20A10 10 0 0112 2zm0 2a8 8 0 100 16A8 8 0 0012 4zm0 3a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H8a1 1 0 110-2h3V8a1 1 0 011-1z"/></svg>
            <span class="text-sm font-semibold text-gray-700">Claude AI — Accounting Assistant</span>
        </div>

        <div class="h-80 overflow-y-auto px-5 py-4 space-y-4" id="chat-log">
            <div class="flex gap-3">
                <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 text-purple-600" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-2.5 text-sm text-gray-700 max-w-md">
                    Hello! I'm your accounting assistant for <strong>{{ $tenant->name }}</strong>. Ask me anything about your chart of accounts, journal entries, or accounting topics.
                </div>
            </div>

            <template x-for="msg in messages" :key="msg.id">
                <div class="flex gap-3" :class="msg.role === 'user' ? 'flex-row-reverse' : ''">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center shrink-0 text-xs font-bold"
                         :class="msg.role === 'user' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'">
                        <span x-text="msg.role === 'user' ? 'You' : 'AI'"></span>
                    </div>
                    <div class="rounded-lg px-4 py-2.5 text-sm max-w-md whitespace-pre-wrap"
                         :class="msg.role === 'user' ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-700'"
                         x-text="msg.content"></div>
                </div>
            </template>

            <template x-if="loading">
                <div class="flex gap-3">
                    <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-purple-600 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-2.5 text-sm text-gray-400 italic">Thinking…</div>
                </div>
            </template>
        </div>
    </div>

    <form @submit.prevent="send" class="flex gap-2">
        <input type="text" x-model="question" placeholder="e.g. What is my current trial balance?" required
               class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
               :disabled="loading">
        <button type="submit" :disabled="loading || !question.trim()"
                class="px-5 py-2.5 text-sm font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50">
            Ask
        </button>
    </form>

    <p class="text-xs text-gray-400 mt-2">Powered by Claude AI · Context: your chart of accounts and recent journal entries</p>
</div>

<script>
function aiChat() {
    return {
        question: '',
        loading: false,
        messages: [],
        nextId: 1,
        send() {
            const q = this.question.trim();
            if (!q || this.loading) return;
            this.messages.push({ id: this.nextId++, role: 'user', content: q });
            this.question = '';
            this.loading = true;
            this.$nextTick(() => this.scrollDown());

            fetch('{{ route('tenant.accounting.ai.query', $tenant->slug) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({ question: q }),
            })
            .then(r => r.json())
            .then(data => {
                this.messages.push({
                    id: this.nextId++,
                    role: 'ai',
                    content: data.answer || data.error || 'No response.',
                });
            })
            .catch(() => {
                this.messages.push({ id: this.nextId++, role: 'ai', content: 'Error connecting to AI service.' });
            })
            .finally(() => {
                this.loading = false;
                this.$nextTick(() => this.scrollDown());
            });
        },
        scrollDown() {
            const log = document.getElementById('chat-log');
            if (log) log.scrollTop = log.scrollHeight;
        },
    };
}
</script>
@endsection
