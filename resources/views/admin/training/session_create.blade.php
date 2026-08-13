@extends('layouts.admin')

@section('title', 'New Training Session')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6" x-data="sessionForm()">

    <div class="mb-6">
        <a href="{{ route('training-sessions.index') }}" class="text-xs text-gray-400 hover:text-gray-600 mb-1 inline-block">← All Sessions</a>
        <h1 class="text-xl font-bold text-gray-800">New Training Session</h1>
        <p class="text-sm text-gray-500 mt-0.5">Create a session and add all attendees at once</p>
    </div>

    <form method="POST" action="{{ route('training-sessions.store') }}" class="space-y-5">
        @csrf

        {{-- Session details --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">Session Details</h2>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Training Programme <span class="text-red-500">*</span></label>
                <input type="text" name="training_type" list="training-types" required
                       placeholder="e.g. AML Awareness, CFT Training"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('training_type') }}">
                <datalist id="training-types">
                    <option value="AML Awareness">
                    <option value="CFT Training">
                    <option value="DPMSR Compliance">
                    <option value="KYC Procedures">
                    <option value="Sanctions Screening">
                    <option value="Risk Assessment">
                    <option value="CBUAE Regulations">
                </datalist>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Training Date <span class="text-red-500">*</span></label>
                    <input type="date" name="training_date" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('training_date') }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Trainer / Provider</label>
                    <input type="text" name="trainer"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('trainer') }}">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Certificate Template</label>
                    <select name="certificate_template" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="1">Classic — Gold &amp; Navy</option>
                        <option value="2">Modern — Blue &amp; White</option>
                        <option value="3">Prestige — Dark</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Signatory Name</label>
                    <input type="text" name="signatory_name"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('signatory_name') }}">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Signatory Title</label>
                <input type="text" name="signatory_title"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       value="{{ old('signatory_title') }}">
            </div>
        </div>

        {{-- Attendees --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700">Attendees</h2>
                <button type="button" @click="addRow()"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Row
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="grid grid-cols-12 gap-2 items-start">
                        <div class="col-span-3">
                            <input type="text" :name="'attendees[' + index + '][employee_name]'" required
                                   placeholder="Full name"
                                   x-model="row.name"
                                   class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="col-span-2">
                            <input type="text" :name="'attendees[' + index + '][employee_id_number]'"
                                   placeholder="ID No."
                                   x-model="row.id_number"
                                   class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="col-span-1">
                            <input type="text" :name="'attendees[' + index + '][employee_role]'"
                                   placeholder="Role"
                                   x-model="row.role"
                                   class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="col-span-3">
                            <select :name="'attendees[' + index + '][crm_client_id]'" required
                                    x-model="row.client_id"
                                    class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="">Company…</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <input type="date" :name="'attendees[' + index + '][expiry_date]'"
                                   x-model="row.expiry"
                                   class="w-full px-2.5 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="col-span-1">
                            <select :name="'attendees[' + index + '][status]'"
                                    x-model="row.status"
                                    class="w-full px-2 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="completed">✓</option>
                                <option value="pending">…</option>
                            </select>
                        </div>
                        <div class="col-span-1 flex items-center pt-1">
                            <button type="button" @click="removeRow(index)" x-show="rows.length > 1"
                                    class="text-gray-300 hover:text-red-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-3 grid grid-cols-12 gap-2">
                <div class="col-span-3 text-xs text-gray-400">Name</div>
                <div class="col-span-2 text-xs text-gray-400">ID Number</div>
                <div class="col-span-1 text-xs text-gray-400">Role</div>
                <div class="col-span-3 text-xs text-gray-400">Company</div>
                <div class="col-span-1 text-xs text-gray-400">Expiry</div>
                <div class="col-span-1 text-xs text-gray-400">Status</div>
            </div>
        </div>

        <button type="submit"
                class="w-full px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
            Create Session
        </button>

    </form>
</div>

<script>
function sessionForm() {
    return {
        rows: [{ name: '', id_number: '', role: '', client_id: '', expiry: '', status: 'completed' }],
        addRow() {
            this.rows.push({ name: '', id_number: '', role: '', client_id: '', expiry: '', status: 'completed' });
        },
        removeRow(index) {
            this.rows.splice(index, 1);
        }
    }
}
</script>
@endsection
