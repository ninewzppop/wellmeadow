@extends('layouts.app')

@section('title', __('Staff Rota'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Staff Rota') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Weekly shift assignments per staff member across all wards.') }}
            </p>
        </div>

        <form method="GET" action="{{ route('rota.index') }}" class="flex items-end gap-2">
            <div>
                <label for="date" class="block text-sm font-medium text-slate-700">{{ __('Filter by week beginning') }}</label>
                <input type="date" id="date" name="date" value="{{ $weekBeginning ?? '' }}"
                       class="mt-1 rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            </div>
            <button type="submit" class="rounded bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">
                {{ __('Filter') }}
            </button>
            @if (!empty($weekBeginning))
                <a href="{{ route('rota.index') }}" class="px-3 py-2 text-sm text-slate-600 hover:text-slate-900">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mt-0.5 h-5 w-5 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
            <div class="mt-3 flex gap-2">
                <a href="#allocation-form"
                   class="rounded bg-rose-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">{{ __('Edit') }}</a>
                <a href="{{ route('rota.index') }}"
                   class="rounded border border-rose-300 px-4 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">{{ __('Cancel') }}</a>
            </div>
        </div>
    @elseif ($conflicts->isNotEmpty())
        <div class="mb-6 flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mt-0.5 h-5 w-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <span>
                <strong>{{ __('Conflict alert:') }}</strong>
                {{ __(':count staff member(s) have more than one assignment in the same week.', ['count' => $conflicts->count()]) }}
            </span>
        </div>
    @endif

    <div id="allocation-form" class="mb-8 rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('New allocation') }}</h2>
        <form method="POST" action="{{ route('rota.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            @csrf
            <div>
                <label for="Stf_No" class="block text-sm font-medium text-slate-700">{{ __('Staff member *') }}</label>
                <select id="Stf_No" name="Stf_No" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    <option value="">{{ __('— select —') }}</option>
                    @foreach ($staff as $member)
                        <option value="{{ $member->Stf_No }}" @selected(old('Stf_No') === $member->Stf_No)>
                            {{ $member->full_name }} ({{ $member->Stf_No }})
                        </option>
                    @endforeach
                </select>
                @error('Stf_No') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="Wd_No" class="block text-sm font-medium text-slate-700">{{ __('Ward *') }}</label>
                <select id="Wd_No" name="Wd_No" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    <option value="">{{ __('— select —') }}</option>
                    @foreach ($wards as $ward)
                        <option value="{{ $ward->Wd_No }}" @selected(old('Wd_No') === $ward->Wd_No)>
                            {{ $ward->Wd_Name }} ({{ $ward->Wd_No }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="WkBegin" class="block text-sm font-medium text-slate-700">{{ __('Week beginning *') }}</label>
                <input type="date" id="WkBegin" name="WkBegin" value="{{ old('WkBegin', $weekBeginning ?? today()->startOfWeek()->toDateString()) }}"
                       class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            </div>
            <div>
                <label for="Shift" class="block text-sm font-medium text-slate-700">{{ __('Shift *') }}</label>
                <select id="Shift" name="Shift" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    @foreach (['Morning', 'Evening', 'Night'] as $shift)
                        <option value="{{ $shift }}" @selected(old('Shift') === $shift)>{{ __($shift) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4">
                <button type="submit" class="rounded bg-sky-600 px-6 py-2 text-sm font-medium text-white hover:bg-sky-700">
                    {{ __('Add allocation') }}
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-slate-200 bg-slate-50 px-4 py-3">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Roster entries') }}</h2>
            <span class="text-xs font-medium text-slate-500">
                {{ __(':count roster allocation(s)', ['count' => $rotas->count()]) }}
                @if (!empty($weekBeginning))
                    {{ __('for week beginning :date', ['date' => \Carbon\Carbon::parse($weekBeginning)->format('d M Y')]) }}
                @endif
            </span>
        </div>

        @if ($rotas->isNotEmpty())
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                        <th class="px-4 py-2">{{ __('Staff') }}</th>
                        <th class="px-4 py-2">{{ __('Position') }}</th>
                        <th class="px-4 py-2">{{ __('Week beginning') }}</th>
                        <th class="px-4 py-2">{{ __('Ward') }}</th>
                        <th class="px-4 py-2">{{ __('Shift') }}</th>
                        <th class="px-4 py-2">{{ __('Status') }}</th>
                        <th class="px-4 py-2 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($rotas as $rota)
                        @php
                            $conflictKey = $rota->Stf_No.'|'.($rota->WkBegin?->toDateString() ?? '');
                            $hasConflict = $conflicts->has($conflictKey);
                            $isEditing = $editId === $rota->StfRota_No;
                        @endphp
                        @if ($isEditing)
                            <tr class="bg-sky-50/60">
                                <td class="px-4 py-2 align-top font-medium text-slate-900 whitespace-nowrap">
                                    {{ $rota->stf?->full_name ?? __('Deleted staff') }}
                                    <form id="rota-edit-{{ $rota->StfRota_No }}" method="POST" action="{{ route('rota.update', $rota) }}" class="hidden">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                </td>
                                <td class="px-4 py-2 align-top text-xs text-slate-500">
                                    @forelse ($rota->stf?->positions ?? [] as $p)
                                        {{ $p->pos->Pos_Name ?? $p->Pos_No }}
                                    @empty
                                        &mdash;
                                    @endforelse
                                </td>
                                <td class="px-2 py-2 align-top">
                                    <input form="rota-edit-{{ $rota->StfRota_No }}" type="date" name="WkBegin" value="{{ old('WkBegin', $rota->WkBegin?->toDateString()) }}"
                                           class="w-full min-w-32 rounded border border-sky-300 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                                </td>
                                <td class="px-2 py-2 align-top">
                                    <select form="rota-edit-{{ $rota->StfRota_No }}" name="Wd_No" class="w-full min-w-28 rounded border border-sky-300 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                                        @foreach ($wards as $ward)
                                            <option value="{{ $ward->Wd_No }}" @selected(old('Wd_No', $rota->Wd_No) === $ward->Wd_No)>
                                                {{ $ward->Wd_Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-2 py-2 align-top">
                                    <select form="rota-edit-{{ $rota->StfRota_No }}" name="Shift" class="w-full min-w-24 rounded border border-sky-300 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                                        @foreach (['Morning', 'Evening', 'Night'] as $shift)
                                            <option value="{{ $shift }}" @selected(old('Shift', $rota->Shift) === $shift)>{{ __($shift) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-2 align-top text-xs text-slate-400">{{ __('editing…') }}</td>
                                <td class="px-4 py-2 align-top whitespace-nowrap text-right">
                                    <button form="rota-edit-{{ $rota->StfRota_No }}" type="submit"
                                            class="rounded bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                        {{ __('Save') }}
                                    </button>
                                    <a href="{{ route('rota.index') }}"
                                       class="ml-1 inline-block rounded border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                                        {{ __('Cancel') }}
                                    </a>
                                </td>
                            </tr>
                        @else
                            <tr class="{{ $hasConflict ? 'bg-rose-50/60' : '' }}">
                                <td class="px-4 py-2 font-medium text-slate-900 whitespace-nowrap">
                                    {{ $rota->stf?->full_name ?? __('Deleted staff') }}
                                </td>
                                <td class="px-4 py-2 text-xs text-slate-500">
                                    @forelse ($rota->stf?->positions ?? [] as $p)
                                        {{ $p->pos->Pos_Name ?? $p->Pos_No }}
                                    @empty
                                        &mdash;
                                    @endforelse
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $rota->WkBegin?->format('d M Y') }}</td>
                                <td class="px-4 py-2">
                                    {{ $rota->wd?->Wd_Name ?? ($rota->Wd_No ?? '&mdash;') }}
                                </td>
                                <td class="px-4 py-2">
                                    <span class="rounded px-2 py-0.5 text-xs font-medium
                                        {{ $rota->Shift === 'Night' ? 'bg-slate-800 text-white' : ($rota->Shift === 'Evening' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800') }}">
                                        {{ $rota->Shift }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    @if ($hasConflict)
                                        <span class="inline-flex items-center gap-1 rounded bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                            </svg>
                                            {{ __('Conflict') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right">
                                    <a href="{{ route('rota.index', array_filter(['edit' => $rota->StfRota_No, 'date' => $weekBeginning])) }}"
                                       class="font-medium text-sky-600 hover:text-sky-800">{{ __('Edit') }}</a>
                                    <form action="{{ route('rota.destroy', $rota) }}" method="POST"
                                          onsubmit="return confirm('{{ addslashes(__('Remove this allocation?')) }}');" class="ml-3 inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="px-4 py-10 text-center text-sm text-slate-400">{{ __('No roster entries yet.') }}</p>
        @endif
    </div>
@endsection
