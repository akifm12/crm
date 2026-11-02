{{-- Users table with an edit panel per user. $users, $roles, $clients (for moving a user), $showClient --}}
@php
    $day = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('j M Y') : '—';
    $ago = fn ($d) => $d ? \Carbon\Carbon::parse($d)->diffForHumans() : 'never';
    $roleNames = ['admin' => 'Admin', 'mlro' => 'MLRO', 'analyst' => 'Analyst'];
@endphp
<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
            <tr><th class="px-4 py-3 text-left">User</th>@if ($showClient)<th class="px-4 py-3 text-left">Client</th>@endif
                <th class="px-4 py-3 text-left">Role</th><th class="px-4 py-3 text-left">Access</th><th class="px-4 py-3 text-left">Screenings</th>
                <th class="px-4 py-3 text-left">Expiry</th><th class="px-4 py-3 text-left">Last sign-in</th><th class="px-4 py-3"></th></tr>
        </thead>
        @forelse ($users as $u)
        <tbody x-data="{ edit: false }" class="border-t border-gray-100">
            <tr class="{{ $u['is_active'] ? '' : 'bg-gray-50 text-gray-400' }}">
                <td class="px-4 py-3"><p class="font-medium {{ $u['is_active'] ? 'text-gray-900' : '' }}">{{ $u['full_name'] ?: '—' }}</p><p class="text-xs text-gray-500">{{ $u['email'] }}</p></td>
                @if ($showClient)
                    <td class="px-4 py-3">@if ($u['company_id'])<a href="{{ route('sentinel-admin.company', $u['company_id']) }}" class="text-blue-600 hover:underline">{{ $u['company_name'] }}</a>@else — @endif</td>
                @endif
                <td class="px-4 py-3">{{ $roleNames[$u['role']] ?? $u['role'] }}</td>
                <td class="px-4 py-3">
                    @if (! $u['is_active'])<span class="text-xs font-medium text-red-600">Deactivated</span>
                    @else
                        @include('admin.sentinel._plan', ['plan' => $u['plan']])
                        @if ($u['monitoring_access'])<span class="ml-1 text-xs text-emerald-600">monitoring</span>@endif
                    @endif
                </td>
                <td class="px-4 py-3">{{ $u['screen_count'] }}{{ $u['quota'] !== null && $u['quota'] < 9999 ? ' / ' . $u['quota'] : '' }}</td>
                <td class="px-4 py-3">{{ $day($u['plan_expiry_date']) }}</td>
                <td class="px-4 py-3">{{ $ago($u['last_login']) }}</td>
                <td class="px-4 py-3 text-right"><button @click="edit = !edit" class="text-sm font-medium text-blue-600 hover:text-blue-800" x-text="edit ? 'Close' : 'Manage'"></button></td>
            </tr>
            <tr x-show="edit" x-cloak class="bg-blue-50/40">
                <td colspan="{{ $showClient ? 8 : 7 }}" class="px-4 py-4">
                    <form method="POST" action="{{ route('sentinel-admin.users.update', $u['id']) }}" class="grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
                        @csrf @method('PATCH')
                        <label class="text-xs text-gray-600 md:col-span-2">Full name
                            <input name="full_name" value="{{ $u['full_name'] }}" class="mt-1 w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg"></label>
                        <label class="text-xs text-gray-600">Role
                            <select name="role" class="mt-1 w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg">
                                @foreach ($roles as $code => $label)<option value="{{ $code }}" @selected($u['role'] === $code)>{{ $label }}</option>@endforeach
                            </select></label>
                        <label class="text-xs text-gray-600">Screening quota
                            <input name="quota" type="number" min="0" value="{{ $u['quota'] }}" class="mt-1 w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg"></label>
                        <label class="text-xs text-gray-600">Plan expiry
                            <input name="expiry_date" type="date" value="{{ $u['plan_expiry_date'] ? substr($u['plan_expiry_date'], 0, 10) : '' }}" class="mt-1 w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg"></label>
                        <label class="text-xs text-gray-600">Monitoring
                            <select name="monitoring_access" class="mt-1 w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg">
                                <option value="1" @selected($u['monitoring_access'])>On</option><option value="0" @selected(! $u['monitoring_access'])>Off</option>
                            </select></label>
                        @if (! empty($clients))
                            <label class="text-xs text-gray-600 md:col-span-2">Client
                                <select name="company_id" class="mt-1 w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg">
                                    @foreach (collect($clients)->sortBy('name') as $c)<option value="{{ $c['id'] }}" @selected($c['id'] == $u['company_id'])>{{ $c['name'] }} ({{ $c['plan'] }})</option>@endforeach
                                </select></label>
                        @endif
                        <div class="md:col-span-4 flex flex-wrap gap-2 justify-end">
                            <button class="px-3 py-1.5 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
                        </div>
                    </form>
                    <div class="mt-3 pt-3 border-t border-gray-200 flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('sentinel-admin.users.password', $u['id']) }}">@csrf
                            <button class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-white" @disabled(! $u['is_active'])>Email password reset link</button></form>
                        <form method="POST" action="{{ route('sentinel-admin.users.update', $u['id']) }}">@csrf @method('PATCH')
                            <input type="hidden" name="is_active" value="{{ $u['is_active'] ? 0 : 1 }}">
                            <button class="px-3 py-1.5 text-sm border rounded-lg {{ $u['is_active'] ? 'border-amber-300 text-amber-700 hover:bg-amber-50' : 'border-emerald-300 text-emerald-700 hover:bg-emerald-50' }}"
                                    @if ($u['is_active']) onclick="return confirm('Deactivate {{ $u['email'] }}? They are signed out and can no longer sign in.')" @endif>
                                {{ $u['is_active'] ? 'Deactivate' : 'Reactivate' }}</button></form>
                        <form method="POST" action="{{ route('sentinel-admin.users.destroy', $u['id']) }}" onsubmit="return confirm('Delete {{ $u['email'] }} permanently? Users with screening records can only be deactivated.')">@csrf @method('DELETE')
                            <button class="px-3 py-1.5 text-sm border border-red-300 text-red-700 rounded-lg hover:bg-red-50">Delete</button></form>
                    </div>
                </td>
            </tr>
        </tbody>
        @empty
            <tbody><tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No users.</td></tr></tbody>
        @endforelse
    </table>
</div>
