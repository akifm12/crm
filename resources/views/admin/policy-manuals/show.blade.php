@extends('layouts.admin')
@section('title', $title . ' | Policy Manuals')
@section('page-title', $title)

@section('content')
<div class="max-w-4xl">
    <a href="{{ route('policy-manuals.index') }}" class="text-xs text-blue-600 hover:underline">&larr; All policy manuals</a>

    @if(session('success'))
    <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    @if(!$hasTemplate)
    <div class="bg-white rounded-xl border border-gray-200 p-8 mt-3 text-center">
        <p class="text-sm text-gray-500">{{ $blurb }}</p>
        <p class="text-xs text-gray-400 mt-4">This manual hasn't been written yet.</p>
    </div>
    @else
    <p class="text-sm text-gray-500 mt-3">{{ $blurb }}</p>

    <div class="bg-white rounded-xl border border-gray-200 p-5 mt-4">
        <label class="block text-xs font-medium text-gray-600 mb-1">Client</label>
        <div class="flex items-center gap-3">
            <select onchange="if(this.value){window.location='{{ route('policy-manuals.show', $slug) }}?tenant='+this.value}"
                    class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                <option value="">Choose a client</option>
                @foreach($tenants as $t)
                <option value="{{ $t->id }}" @selected($tenant && $tenant->id === $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
            <a href="{{ route('policy-manuals.template', $slug) }}" class="text-xs text-blue-600 hover:underline whitespace-nowrap">Download blank template</a>
        </div>
    </div>

    @if($tenant)
    <form method="POST" action="{{ route('policy-manuals.generate', $slug) }}" class="mt-4 space-y-4">
        @csrf
        <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">

        @foreach($fields as $group => $items)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ $group }}</h3>
            <div class="grid grid-cols-2 gap-4">
                @foreach($items as $key => [$label, $hint, $type])
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $label }}</label>
                    @if($type === 'select')
                    <select name="f[{{ $key }}]" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white">
                        @foreach($appointmentOptions as $opt)
                        <option value="{{ $opt }}" @selected(($values[$key] ?? '') === $opt)>{{ ucfirst($opt) }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="text" name="f[{{ $key }}]" value="{{ old("f.$key", $values[$key] ?? '') }}" maxlength="300"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg {{ ($values[$key] ?? '') === '' ? 'bg-yellow-50' : '' }}">
                    @endif
                    @if($hint)<p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>@endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <p class="text-xs text-gray-500">Fields left empty stay as highlighted placeholders in the Word document so the gaps are easy to see. Details are saved on the client record and reused for the other manuals.</p>

        <div class="flex items-center gap-3">
            <button type="submit" name="action" value="download" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Save &amp; download Word document</button>
            <button type="submit" name="action" value="save" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">Save only</button>
        </div>
    </form>
    @else
    <p class="text-xs text-gray-400 mt-4">Choose a client to fill in their details and generate their manual.</p>
    @endif
    @endif
</div>
@endsection
