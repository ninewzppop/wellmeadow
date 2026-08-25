@extends('layouts.app')

@section('title', __('Allergies'))

@section('content')
    @php
        $severityClasses = [
            'Severe' => 'bg-red-100 text-red-700',
            'Moderate' => 'bg-amber-100 text-amber-700',
            'Mild' => 'bg-green-100 text-green-700',
        ];
    @endphp

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Allergies') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Manage patient allergy records from the PatientAllergy table') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="inline-flex rounded-full border border-slate-200 bg-white p-1 shadow-sm"
                 role="group" aria-label="{{ __('View mode') }}">
                <a href="{{ request()->fullUrlWithQuery(['view' => 'grouped', 'page' => null]) }}"
                   class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition @if ($viewMode === 'grouped') bg-slate-900 text-white shadow @else text-slate-500 hover:bg-slate-100 hover:text-slate-700 @endif">
                    {{ __('By Patient') }}
                </a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'flat', 'page' => null]) }}"
                   class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition @if ($viewMode === 'flat') bg-slate-900 text-white shadow @else text-slate-500 hover:bg-slate-100 hover:text-slate-700 @endif">
                    {{ __('By Record') }}
                </a>
            </div>
            <a href="{{ route('allergies.create') }}"
               class="flex items-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                <span aria-hidden="true">+</span> {{ __('New Allergy Record') }}
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="view" value="{{ $viewMode }}">

        <div class="min-w-[220px] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
            <input type="search" name="search" value="{{ request('search') }}"
                   placeholder="{{ __('Allergy No., name, reaction, patient, drug...') }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        </div>

        <div class="min-w-[160px]">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Severity') }}</label>
            <select name="severity" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                <option value="">{{ __('All') }}</option>
                @foreach ($severities as $severity)
                    <option value="{{ $severity }}" @selected(request('severity') === $severity)>{{ __($severity) }}</option>
                @endforeach
            </select>
        </div>

        <div class="min-w-[220px]">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Patient') }}</label>
            <select name="patient" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                <option value="">{{ __('All') }}</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->Pt_No }}" @selected(request('patient') == $patient->Pt_No)>
                        {{ $patient->full_name }} ({{ $allergyTotals->get($patient->Pt_No, 0) }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                {{ __('Filter') }}
            </button>
            <a href="{{ route('allergies.index', ['view' => $viewMode]) }}"
               class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                {{ __('Clear') }}
            </a>
        </div>
    </form>

    @php
        $isEmpty = $viewMode === 'flat'
            ? $allergies->isEmpty()
            : $patientGroups->isEmpty();
    @endphp

    @if ($isEmpty)
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-400 shadow-sm">
            {{ __('No allergy records found.') }}
        </div>
    @elseif ($viewMode === 'flat')
        {{-- Mode B: Flat list (1 row = 1 record) --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-6 py-4">{{ __('Allergy No.') }}</th>
                            <th class="px-6 py-4">{{ __('Patient') }}</th>
                            <th class="px-6 py-4">{{ __('Allergen') }}</th>
                            <th class="px-6 py-4">{{ __('Reaction') }}</th>
                            <th class="px-6 py-4">{{ __('Severity') }}</th>
                            <th class="px-6 py-4">{{ __('Diagnosed') }}</th>
                            <th class="px-6 py-4">{{ __('Recorded By') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($allergies as $allergy)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-slate-700">{{ $allergy->Allergy_No }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    @if ($allergy->patient)
                                        <a href="{{ route('patients.show', $allergy->patient) }}" class="hover:text-blue-600">
                                            {{ $allergy->patient->full_name }}
                                        </a><br>
                                        <span class="text-xs text-slate-400">{{ $allergy->patient->Pt_No }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $allergy->drug?->Name ?? $allergy->Allergy_Name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $allergy->Reaction }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $severityClasses[$allergy->Severity] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ __($allergy->Severity) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $allergy->DiagDate->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $allergy->recordedBy?->full_name ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('allergies.edit', $allergy) }}"
                                           title="{{ __('Edit') }}"
                                           class="text-slate-400 hover:text-slate-700">
                                            &#9998;
                                        </a>
                                        <form method="POST" action="{{ route('allergies.destroy', $allergy) }}"
                                              onsubmit="return confirm('{{ __('Delete this allergy record?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="{{ __('Delete') }}"
                                                    class="text-red-400 hover:text-red-600">
                                                &#128465;
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 flex justify-center">
            {{ $allergies->links() }}
        </div>
    @else
        {{-- Mode A: Grouped by patient (desktop table) --}}
        <div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="w-[22%] px-6 py-4">{{ __('Patient') }}</th>
                        <th class="px-6 py-4">{{ __('Allergen') }}</th>
                        <th class="px-6 py-4">{{ __('Reaction') }}</th>
                        <th class="px-6 py-4">{{ __('Severity') }}</th>
                        <th class="px-6 py-4">{{ __('Diagnosed') }}</th>
                        <th class="px-6 py-4">{{ __('Recorded By') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                @foreach ($patientGroups as $index => $group)
                    <tbody class="border-l-4 {{ $group['hasSevere'] ? 'border-red-500' : 'border-transparent' }} divide-y divide-slate-100 {{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }}">
                        @foreach ($group['allergies'] as $i => $allergy)
                            <tr class="hover:bg-slate-100/60">
                                @if ($i === 0)
                                    <td rowspan="{{ $group['count'] }}" class="px-6 py-4 align-top">
                                        <div class="flex items-center gap-1.5">
                                            @if ($group['patient'])
                                                <a href="{{ route('patients.show', $group['patient']) }}"
                                                   class="font-semibold text-slate-900 hover:text-blue-600">
                                                    {{ $group['patient']->full_name }}
                                                </a>
                                                @if ($group['hasSevere'])
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                         class="h-4 w-4 shrink-0 text-red-500" role="img" aria-label="{{ __('Has severe allergy') }}">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                                    </svg>
                                                @endif
                                            @else
                                                <span class="font-semibold italic text-slate-500">{{ __('No Patient') }}</span>
                                            @endif
                                        </div>
                                        @if ($group['patient'])
                                            <span class="text-xs text-slate-400">{{ $group['patient']->Pt_No }}</span>
                                        @endif
                                        <br>
                                        <span class="mt-1.5 inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/10">
                                            {{ $group['count'] }} {{ $group['count'] === 1 ? __('allergy') : __('allergies') }}
                                        </span>
                                    </td>
                                @endif
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $allergy->drug?->Name ?? $allergy->Allergy_Name ?? '—' }}
                                    <span class="ml-1 text-xs text-slate-400">#{{ $allergy->Allergy_No }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $allergy->Reaction }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $severityClasses[$allergy->Severity] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ __($allergy->Severity) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $allergy->DiagDate->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $allergy->recordedBy?->full_name ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('allergies.edit', $allergy) }}"
                                           title="{{ __('Edit') }}"
                                           class="text-slate-400 hover:text-slate-700">
                                            &#9998;
                                        </a>
                                        <form method="POST" action="{{ route('allergies.destroy', $allergy) }}"
                                              onsubmit="return confirm('{{ __('Delete this allergy record?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="{{ __('Delete') }}"
                                                    class="text-red-400 hover:text-red-600">
                                                &#128465;
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach
            </table>
        </div>

        {{-- Mode A: Grouped by patient (mobile cards) --}}
        <div class="space-y-4 md:hidden">
            @foreach ($patientGroups as $group)
                <div class="flex overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="w-1 shrink-0 {{ $group['hasSevere'] ? 'bg-red-500' : 'bg-transparent' }}"></div>
                    <div class="min-w-0 flex-1 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex min-w-0 items-center gap-1.5">
                                @if ($group['patient'])
                                    <a href="{{ route('patients.show', $group['patient']) }}"
                                       class="truncate font-semibold text-slate-900 hover:text-blue-600">
                                        {{ $group['patient']->full_name }}
                                    </a>
                                    @if ($group['hasSevere'])
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                             class="h-4 w-4 shrink-0 text-red-500" role="img" aria-label="{{ __('Has severe allergy') }}">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                        </svg>
                                    @endif
                                @else
                                    <span class="font-semibold italic text-slate-500">{{ __('No Patient') }}</span>
                                @endif
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/10">
                                {{ $group['count'] }} {{ $group['count'] === 1 ? __('allergy') : __('allergies') }}
                            </span>
                        </div>
                        @if ($group['patient'])
                            <span class="text-xs text-slate-400">{{ $group['patient']->Pt_No }}</span>
                        @endif

                        <ul class="mt-3 space-y-3 border-t border-slate-100 pt-3">
                            @foreach ($group['allergies'] as $allergy)
                                <li class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-slate-800">
                                            {{ $allergy->drug?->Name ?? $allergy->Allergy_Name ?? '—' }}
                                            <span class="text-xs font-normal text-slate-400">#{{ $allergy->Allergy_No }}</span>
                                        </p>
                                        <p class="mt-0.5 truncate text-xs text-slate-500">{{ $allergy->Reaction }}</p>
                                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $severityClasses[$allergy->Severity] ?? 'bg-slate-100 text-slate-600' }}">
                                                {{ __($allergy->Severity) }}
                                            </span>
                                            <span class="text-xs text-slate-400">{{ $allergy->DiagDate->format('d/m/Y') }}</span>
                                            <span class="text-xs text-slate-400">{{ $allergy->recordedBy?->full_name ?? '—' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3 pt-1">
                                        <a href="{{ route('allergies.edit', $allergy) }}"
                                           title="{{ __('Edit') }}"
                                           class="text-slate-400 hover:text-slate-700">
                                            &#9998;
                                        </a>
                                        <form method="POST" action="{{ route('allergies.destroy', $allergy) }}"
                                              onsubmit="return confirm('{{ __('Delete this allergy record?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="{{ __('Delete') }}"
                                                    class="text-red-400 hover:text-red-600">
                                                &#128465;
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex justify-center">
            {{ $patientGroups->links() }}
        </div>
    @endif
@endsection
