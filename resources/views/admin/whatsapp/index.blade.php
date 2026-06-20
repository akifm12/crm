@extends('layouts.admin')
@section('title', 'WhatsApp — BlueArrow Portal')
@section('page-title', 'WhatsApp')
@section('page-subtitle', 'Send and schedule messages to tenant groups')

@section('content')

<style>
.wa-log { font-family:'Courier New',monospace;font-size:11px;line-height:1.8;background:#0f172a;color:#94a3b8;border-radius:10px;padding:16px;max-height:420px;overflow-y:auto; }
</style>

<div x-data="waManager()" x-init="init()" x-cloak>

    {{-- Toasts --}}
    <div class="fixed top-5 right-5 z-50 space-y-2 pointer-events-none">
        <template x-for="t in toasts" :key="t.id">
            <div class="pointer-events-auto px-4 py-2.5 rounded-lg text-sm font-medium text-white shadow-lg transition"
                 :class="{'bg-green-600': t.type==='success', 'bg-red-600': t.type==='error', 'bg-blue-600': t.type==='info'}"
                 x-text="t.msg"></div>
        </template>
    </div>

    {{-- Tab bar --}}
    <div class="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 mb-5 overflow-x-auto">
        <template x-for="t in tabs" :key="t.id">
            <button @click="tab = t.id"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap"
                    :class="tab === t.id ? 'bg-green-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    x-text="t.label"></button>
        </template>
    </div>

    {{-- ── CONNECTION TAB ───────────────────────────────────────────────── --}}
    <div x-show="tab === 'connection'" class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Status card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Connection status</h3>

            <div class="flex items-center gap-3 p-4 rounded-xl mb-4 border"
                 :class="status?.isReady ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200'">
                <div class="w-3 h-3 rounded-full flex-shrink-0"
                     :class="status?.isReady ? 'bg-green-500' : 'bg-gray-400'"
                     :style="status?.isReady ? 'box-shadow:0 0 0 4px rgba(74,222,128,0.2)' : ''"></div>
                <div>
                    <p class="text-sm font-semibold" :class="status?.isReady ? 'text-green-700' : 'text-gray-600'">
                        <span x-show="statusLoading">Checking…</span>
                        <span x-show="!statusLoading && status?.isReady">Connected</span>
                        <span x-show="!statusLoading && !status?.isReady && status?.qrImage">Waiting for QR scan</span>
                        <span x-show="!statusLoading && !status?.isReady && !status?.qrImage">Disconnected</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5" x-show="status?.groupCount !== undefined"
                       x-text="status?.groupCount + ' groups loaded'"></p>
                </div>
            </div>

            {{-- QR code --}}
            <div x-show="status?.qrImage && !status?.isReady" class="mb-4 text-center">
                <p class="text-xs text-gray-500 mb-3">Open WhatsApp → Linked Devices → Link a device → scan</p>
                <div class="inline-block p-3 bg-white border-2 border-gray-200 rounded-xl">
                    <img :src="status?.qrImage" alt="QR Code" width="220" height="220">
                </div>
                <p class="text-xs text-amber-600 mt-2">QR refreshes every 20 seconds</p>
            </div>

            {{-- Waiting for QR after reconnect --}}
            <div x-show="!status?.isReady && !status?.qrImage && waitingForQr" class="mb-4 text-center">
                <div class="inline-flex flex-col items-center gap-3 p-6 bg-blue-50 border border-blue-200 rounded-xl">
                    <svg class="animate-spin w-6 h-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-sm text-blue-700 font-medium">Waiting for QR code…</p>
                    <p class="text-xs text-blue-500">This can take 5–15 seconds</p>
                </div>
            </div>

            {{-- Prompt to reconnect --}}
            <div x-show="!status?.isReady && !status?.qrImage && !waitingForQr" class="mb-4">
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                    Not connected — click <strong>Reconnect</strong> below to generate a QR code.
                </div>
            </div>

            <div class="flex gap-2 flex-wrap">
                <button @click="fetchStatus()" class="px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                    ↻ Refresh
                </button>
                <button x-show="status?.isReady" @click="disconnect()"
                        class="px-3 py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50">
                    Disconnect
                </button>
                <button x-show="!status?.isReady" @click="reconnect()"
                        class="px-3 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">
                    Reconnect
                </button>
            </div>
        </div>

        {{-- Info card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Info</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Service</span>
                    <span class="text-sm font-mono text-gray-600">127.0.0.1:3001</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Groups loaded</span>
                    <span class="text-sm font-medium text-gray-700" x-text="status?.groupCount ?? '—'"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Status</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                          :class="status?.isReady ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                          x-text="status?.isReady ? 'Ready' : 'Not connected'"></span>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 leading-relaxed mb-2">
                    Do not open WhatsApp Web on the same number — it will break the session.
                    Use this dashboard to manage all messages and schedules.
                </p>
                <details class="mt-2">
                    <summary class="text-xs text-gray-400 cursor-pointer hover:text-gray-600">Raw API response (debug)</summary>
                    <pre class="mt-1 text-xs bg-gray-50 rounded p-2 overflow-auto max-h-48 text-gray-600" x-text="JSON.stringify(status, null, 2)"></pre>
                </details>
            </div>
        </div>
    </div>

    {{-- ── GROUPS TAB ───────────────────────────────────────────────────── --}}
    <div x-show="tab === 'groups'">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <input x-model="groupsFilter" type="text" placeholder="Search groups…"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-56">
                <span class="text-xs font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded-full"
                      x-text="filteredGroups.length + ' groups'"></span>
            </div>
            <button @click="refreshGroups()" :disabled="groupsRefreshing"
                    class="px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 flex items-center gap-2">
                <span x-show="groupsRefreshing">Refreshing…</span>
                <span x-show="!groupsRefreshing">↻ Refresh from WhatsApp</span>
            </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div x-show="groupsLoading" class="text-center py-12 text-gray-400 text-sm">Loading groups…</div>
            <div x-show="!groupsLoading && filteredGroups.length === 0" class="text-center py-12 text-gray-400 text-sm">
                No groups found. Connect WhatsApp and click Refresh.
            </div>
            <table x-show="!groupsLoading && filteredGroups.length > 0" class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Group name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Members</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="g in filteredGroups" :key="g.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800" x-text="g.name"></td>
                            <td class="px-4 py-3 text-gray-500" x-text="g.members"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── SEND TAB ─────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'send'" class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Group picker --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Select groups</h3>
                <span x-show="sendSelected.length > 0"
                      class="text-xs font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full"
                      x-text="sendSelected.length + ' selected'"></span>
            </div>

            <div x-show="groups.length === 0" class="text-sm text-gray-400">
                No groups loaded — go to Groups tab and refresh.
            </div>

            <div class="space-y-1.5 max-h-96 overflow-y-auto">
                <template x-for="g in groups" :key="g.id">
                    <label class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition border"
                           :class="sendSelected.includes(g.id) ? 'bg-green-50 border-green-200' : 'hover:bg-gray-50 border-transparent'">
                        <input type="checkbox" :value="g.id"
                               :checked="sendSelected.includes(g.id)"
                               @change="toggleSendGroup(g.id)"
                               class="rounded border-gray-300 text-green-600">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate" x-text="g.name"></p>
                            <p class="text-xs text-gray-400" x-text="g.members + ' members'"></p>
                        </div>
                    </label>
                </template>
            </div>

            <div class="mt-3 pt-3 border-t border-gray-100 flex gap-2">
                <button @click="sendSelected = groups.map(g => g.id)"
                        class="text-xs text-blue-600 hover:underline">Select all</button>
                <span class="text-gray-300">|</span>
                <button @click="sendSelected = []"
                        class="text-xs text-gray-500 hover:underline">Clear</button>
            </div>
        </div>

        {{-- Message --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Message</h3>
                <textarea x-model="sendMessage" rows="8"
                          placeholder="Type your WhatsApp message…&#10;&#10;You can use *bold*, _italic_, ~strikethrough~ formatting."
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white resize-none"></textarea>
                <div class="flex items-center justify-between mt-3">
                    <span class="text-xs text-gray-400" x-text="sendMessage.length + ' characters'"></span>
                    <button @click="sendNow()"
                            :disabled="sending || !sendMessage.trim() || sendSelected.length === 0"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-40 flex items-center gap-2">
                        <span x-show="sending">Sending…</span>
                        <span x-show="!sending" x-text="'▶ Send to ' + sendSelected.length + ' group(s)'"></span>
                    </button>
                </div>
            </div>

            <div x-show="sendResult" class="rounded-xl border p-4"
                 :class="sendResult?.error ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200'">
                <p class="text-sm font-medium"
                   :class="sendResult?.error ? 'text-red-700' : 'text-green-700'"
                   x-text="sendResult?.error ? '✕ ' + sendResult.error : '✓ Queued for ' + sendSelected.length + ' group(s)'"></p>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <p class="text-xs font-semibold text-amber-700 mb-1">Note</p>
                <p class="text-xs text-amber-600 leading-relaxed">
                    Messages are sent with an 8-second delay between groups to avoid rate limiting.
                </p>
            </div>
        </div>
    </div>

    {{-- ── SCHEDULES TAB ────────────────────────────────────────────────── --}}
    <div x-show="tab === 'schedules'">

        <div class="flex items-center justify-between mb-5">
            <span class="text-xs font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full"
                  x-text="schedules.length + ' schedules'"></span>
            <button @click="showScheduleForm = !showScheduleForm"
                    class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <span x-text="showScheduleForm ? '✕ Cancel' : '+ New schedule'"></span>
            </button>
        </div>

        {{-- Schedule form --}}
        <div x-show="showScheduleForm" class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">New scheduled message</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Label *</label>
                    <input x-model="scheduleForm.label" type="text" placeholder="e.g. Monday goAML Reminder"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <select x-model="scheduleForm.type"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="weekly">Weekly (recurring)</option>
                        <option value="once">One-time</option>
                    </select>
                </div>

                {{-- Weekly options --}}
                <div x-show="scheduleForm.type === 'weekly'">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Day of week</label>
                    <select x-model="scheduleForm.dayOfWeek"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="0">Sunday</option>
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                    </select>
                </div>

                {{-- One-time date --}}
                <div x-show="scheduleForm.type === 'once'">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                    <input x-model="scheduleForm.date" type="date"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Time</label>
                    <div class="flex gap-2">
                        <input x-model="scheduleForm.hour" type="number" min="0" max="23" placeholder="HH"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="flex items-center text-gray-400 font-bold">:</span>
                        <input x-model="scheduleForm.minute" type="number" min="0" max="59" placeholder="MM"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Message *</label>
                <textarea x-model="scheduleForm.message" rows="4" placeholder="Message content…"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-2">
                    Target groups *
                    <span class="font-normal text-gray-400" x-text="'(' + scheduleForm.groupIds.length + ' selected)'"></span>
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto border border-gray-100 rounded-lg p-2">
                    <template x-for="g in groups" :key="g.id">
                        <label class="flex items-center gap-2 p-2 rounded-lg cursor-pointer text-xs transition border"
                               :class="scheduleForm.groupIds.includes(g.id) ? 'bg-blue-50 border-blue-200' : 'hover:bg-gray-50 border-transparent'">
                            <input type="checkbox" :checked="scheduleForm.groupIds.includes(g.id)"
                                   @change="toggleScheduleGroup(g.id)" class="rounded">
                            <span class="truncate font-medium text-gray-700" x-text="g.name"></span>
                        </label>
                    </template>
                    <p x-show="groups.length === 0" class="text-xs text-gray-400 col-span-3 p-2">
                        No groups — go to Groups tab and refresh first.
                    </p>
                </div>
            </div>

            <div class="flex gap-2 justify-end">
                <button @click="showScheduleForm = false"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button @click="addSchedule()" :disabled="savingSchedule"
                        class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-40">
                    <span x-text="savingSchedule ? 'Saving…' : 'Add schedule'"></span>
                </button>
            </div>
        </div>

        {{-- Schedule list --}}
        <div x-show="schedulesLoading" class="text-center py-12 text-gray-400 text-sm">Loading…</div>

        <div x-show="!schedulesLoading && schedules.length === 0"
             class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center text-gray-400 text-sm">
            No schedules yet. Add one above.
        </div>

        <div class="space-y-3">
            <template x-for="s in schedules" :key="s.id">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="font-semibold text-gray-800" x-text="s.label"></p>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Active</span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                      :class="s.type === 'once' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                                      x-text="s.type === 'once' ? 'One-time' : 'Weekly'"></span>
                            </div>
                            <p class="text-xs text-gray-500 mb-2">
                                <span x-show="s.type !== 'once'" x-text="'Every ' + dayName(s.dayOfWeek) + ' at ' + pad(s.hour) + ':' + pad(s.minute)"></span>
                                <span x-show="s.type === 'once'" x-text="'Once on ' + s.date + ' at ' + pad(s.hour) + ':' + pad(s.minute)"></span>
                            </p>
                            <p class="text-sm text-gray-600 mb-2 line-clamp-2" x-text="s.message"></p>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="id in (s.groupIds || [])" :key="id">
                                    <span class="text-xs bg-green-50 text-green-700 border border-green-100 px-2 py-0.5 rounded-full"
                                          x-text="groupName(id)"></span>
                                </template>
                            </div>
                        </div>
                        <button @click="deleteSchedule(s.id)"
                                class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 flex-shrink-0">
                            Delete
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ── LOGS TAB ─────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'logs'">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-sm font-semibold text-gray-700">Activity log</h3>
            <button @click="fetchLogs()"
                    class="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 bg-white">
                ↻ Refresh
            </button>
        </div>
        <div class="wa-log">
            <span x-show="logsLoading" style="color:#60a5fa">Loading…</span>
            <span x-show="!logsLoading && logs.length === 0" style="color:#60a5fa">No activity yet.</span>
            <template x-for="(line, i) in logs" :key="i">
                <div :style="logColor(line)" x-text="line"></div>
            </template>
        </div>
        <p class="text-xs text-gray-400 mt-2">Auto-refreshes every 10 seconds. Showing last 100 entries.</p>
    </div>

</div>

<script>
function waManager() {
    return {
        tab: 'connection',
        tabs: [
            {id:'connection', label:'Connection'},
            {id:'groups',     label:'Groups'},
            {id:'send',       label:'Send Message'},
            {id:'schedules',  label:'Schedules'},
            {id:'logs',       label:'Activity Log'},
        ],

        // Connection
        status: null,
        statusLoading: true,
        waitingForQr: false,

        // Groups
        groups: [],
        groupsLoading: true,
        groupsFilter: '',
        groupsRefreshing: false,

        // Send
        sendSelected: [],
        sendMessage: '',
        sending: false,
        sendResult: null,

        // Schedules
        schedules: [],
        schedulesLoading: true,
        showScheduleForm: false,
        savingSchedule: false,
        scheduleForm: { label:'', type:'weekly', dayOfWeek:'1', date:'', hour:9, minute:0, message:'', groupIds:[] },

        // Logs
        logs: [],
        logsLoading: true,

        // Toasts
        toasts: [],

        get filteredGroups() {
            if (!this.groupsFilter) return this.groups;
            const q = this.groupsFilter.toLowerCase();
            return this.groups.filter(g => g.name?.toLowerCase().includes(q));
        },

        async init() {
            await Promise.all([this.fetchStatus(), this.fetchGroups(), this.fetchSchedules(), this.fetchLogs()]);
            setInterval(() => this.fetchStatus(), 5000);
            setInterval(() => this.fetchLogs(), 10000);
        },

        async fetchStatus() {
            try {
                const r = await fetch('/whatsapp/api/status');
                this.status = await r.json();
                console.log('[WA status]', JSON.stringify(this.status));
            } catch(e) { this.status = {error: e.message}; }
            this.statusLoading = false;
        },

        async fetchGroups() {
            try {
                const r = await fetch('/whatsapp/api/groups');
                const d = await r.json();
                this.groups = Array.isArray(d) ? d : [];
            } catch {}
            this.groupsLoading = false;
        },

        async refreshGroups() {
            this.groupsRefreshing = true;
            try {
                await fetch('/whatsapp/api/groups/refresh', {method:'POST'});
                this.toast('Refreshing groups from WhatsApp…', 'info');
                setTimeout(() => this.fetchGroups(), 3000);
            } catch { this.toast('Refresh failed', 'error'); }
            this.groupsRefreshing = false;
        },

        async fetchSchedules() {
            try {
                const r = await fetch('/whatsapp/api/schedules');
                const d = await r.json();
                this.schedules = Array.isArray(d) ? d : [];
            } catch {}
            this.schedulesLoading = false;
        },

        async fetchLogs() {
            try {
                const r = await fetch('/whatsapp/api/logs');
                const d = await r.json();
                this.logs = Array.isArray(d) ? d : [];
            } catch {}
            this.logsLoading = false;
        },

        async reconnect() {
            await fetch('/whatsapp/api/reconnect', {method:'POST'});
            this.toast('Reconnecting…', 'info');
            this.waitingForQr = true;
            let polls = 0;
            const fastPoll = setInterval(async () => {
                await this.fetchStatus();
                polls++;
                if (polls >= 30 || this.status?.isReady || this.status?.qrImage) {
                    clearInterval(fastPoll);
                    this.waitingForQr = false;
                }
            }, 2000);
        },

        async disconnect() {
            if (!confirm('Disconnect WhatsApp? You will need to scan the QR code again.')) return;
            await fetch('/whatsapp/api/disconnect', {method:'POST'});
            this.toast('Disconnected', 'info');
            setTimeout(() => this.fetchStatus(), 2000);
        },

        toggleSendGroup(id) {
            const idx = this.sendSelected.indexOf(id);
            idx === -1 ? this.sendSelected.push(id) : this.sendSelected.splice(idx, 1);
        },

        async sendNow() {
            if (!this.sendMessage.trim()) { this.toast('Message is required', 'error'); return; }
            if (this.sendSelected.length === 0) { this.toast('Select at least one group', 'error'); return; }
            if (!confirm(`Send to ${this.sendSelected.length} group(s)?`)) return;
            this.sending = true; this.sendResult = null;
            try {
                const r = await fetch('/whatsapp/api/send/immediate', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content},
                    body: JSON.stringify({groupIds: this.sendSelected, message: this.sendMessage}),
                });
                this.sendResult = await r.json();
                if (!this.sendResult.error) {
                    this.toast(`Queued for ${this.sendSelected.length} group(s)`, 'success');
                    this.sendSelected = [];
                    this.sendMessage = '';
                }
            } catch(e) { this.toast('Send failed: ' + e.message, 'error'); }
            this.sending = false;
        },

        toggleScheduleGroup(id) {
            const idx = this.scheduleForm.groupIds.indexOf(id);
            idx === -1 ? this.scheduleForm.groupIds.push(id) : this.scheduleForm.groupIds.splice(idx, 1);
        },

        async addSchedule() {
            if (!this.scheduleForm.label.trim()) { this.toast('Label is required', 'error'); return; }
            if (!this.scheduleForm.message.trim()) { this.toast('Message is required', 'error'); return; }
            if (this.scheduleForm.groupIds.length === 0) { this.toast('Select at least one group', 'error'); return; }
            this.savingSchedule = true;
            try {
                const r = await fetch('/whatsapp/api/schedules', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content},
                    body: JSON.stringify(this.scheduleForm),
                });
                const d = await r.json();
                if (d.ok) {
                    this.toast('Schedule added', 'success');
                    this.showScheduleForm = false;
                    this.scheduleForm = {label:'',type:'weekly',dayOfWeek:'1',date:'',hour:9,minute:0,message:'',groupIds:[]};
                    await this.fetchSchedules();
                } else {
                    this.toast(d.error || 'Failed to add schedule', 'error');
                }
            } catch(e) { this.toast('Failed: ' + e.message, 'error'); }
            this.savingSchedule = false;
        },

        async deleteSchedule(id) {
            if (!confirm('Delete this schedule?')) return;
            await fetch('/whatsapp/api/schedules/' + id, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content},
            });
            this.toast('Schedule deleted', 'success');
            await this.fetchSchedules();
        },

        groupName(id) {
            return this.groups.find(g => g.id === id)?.name || id;
        },

        dayName(d) {
            return ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][parseInt(d)] || d;
        },

        pad(n) { return String(n).padStart(2, '0'); },

        logColor(line) {
            if (line.includes('✅') || line.includes('Connected')) return 'color:#4ade80';
            if (line.includes('❌') || line.includes('Failed')) return 'color:#f87171';
            if (line.includes('⚠')) return 'color:#fbbf24';
            if (line.includes('⏰') || line.includes('📋') || line.includes('📱')) return 'color:#60a5fa';
            return 'color:#94a3b8';
        },

        toast(msg, type='info') {
            const id = Date.now() + Math.random();
            this.toasts.push({id, msg, type});
            setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 3500);
        },
    };
}
</script>

@endsection
