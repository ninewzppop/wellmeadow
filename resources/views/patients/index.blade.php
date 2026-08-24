@extends('layouts.app')

@section('title', __('Patients'))

@section('content')
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Patients') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Manage hospital patient records and registrations') }}
            </p>
        </div>
        <a href="{{ route('patients.create') }}"
           class="flex items-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
            <span aria-hidden="true">+</span> {{ __('Register Patient') }}
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-4 flex gap-2">
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="{{ __('Search by ID, name, phone...') }}"
            class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
        >
        <button type="submit"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            {{ __('Search') }}
        </button>
        @if (request('search'))
            <a href="{{ route('patients.index') }}"
               class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                {{ __('Clear') }}
            </a>
        @endif
    </form>

    @if ($patients->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-400 shadow-sm">
            {{ __('No patients found.') }}
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-6 py-4">{{ __('Patient No.') }}</th>
                            <th class="px-6 py-4">{{ __('Name') }}</th>
                            <th class="px-6 py-4">{{ __('DOB') }}</th>
                            <th class="px-6 py-4">{{ __('Sex') }}</th>
                            <th class="px-6 py-4">{{ __('Marital Status') }}</th>
                            <th class="px-6 py-4">{{ __('Local Doctor') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($patients as $patient)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-slate-700">{{ $patient->Pt_No }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $patient->full_name }}</td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $patient->DOB ? $patient->DOB->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $patient->Sex === 'M' ? __('Male') : ($patient->Sex === 'F' ? __('Female') : '-') }}</td>
                                <td class="px-6 py-4">
                                    @if ($patient->MaritalStat)
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            {{ $patient->MaritalStat }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $patient->localDoctor?->full_name ?? '-' }}</td>
<td class="px-6 py-4">
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('patients.show', $patient) }}"
           title="{{ __('View') }}"
           class="text-slate-400 hover:text-slate-700">
            &#128065;
        </a>
        <a href="{{ route('patients.edit', $patient) }}"
           title="{{ __('Edit') }}"
           class="text-slate-400 hover:text-slate-700">
            &#9998;
        </a>
        <form method="POST" action="{{ route('patients.destroy', $patient) }}"
              onsubmit="return confirm('{{ __('Delete this patient?') }}')">
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
            {{ $patients->links() }}
        </div>
    @endif
@endsection