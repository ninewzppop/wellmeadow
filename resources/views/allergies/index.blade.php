@extends('layouts.app')

@section('title', __('Allergies'))

@section('content')
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Allergies') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Manage patient allergy records from the PatientAllergy table') }}
            </p>
        </div>
        <a href="{{ route('allergies.create') }}"
           class="flex items-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
            <span aria-hidden="true">+</span> {{ __('New Allergy Record') }}
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
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

        <div class="min-w-[200px]">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Patient') }}</label>
            <select name="patient" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                <option value="">{{ __('All') }}</option>
                @foreach ($patients as $patient)
                    <option value="{{ $patient->Pt_No }}" @selected(request('patient') == $patient->Pt_No)>
                        {{ $patient->full_name }} ({{ $patient->Pt_No }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                {{ __('Filter') }}
            </button>
            <a href="{{ route('allergies.index') }}"
               class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                {{ __('Clear') }}
            </a>
        </div>
    </form>

    @if ($allergies->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-400 shadow-sm">
            {{ __('No allergy records found.') }}
        </div>
    @else
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
                            @php
                                $severityClass = match($allergy->Severity) {
                                    'Severe' => 'bg-red-100 text-red-700',
                                    'Moderate' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-sky-100 text-sky-700',
                                };
                            @endphp
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
                                    {{ $allergy->Allergy_Name ?? $allergy->drug?->Name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $allergy->Reaction }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $severityClass }}">
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
    @endif
@endsection
