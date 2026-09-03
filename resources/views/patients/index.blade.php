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

    {{-- Search + Sort/Filter --}}
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="min-w-[200px]">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by ID, name, phone...') }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Order by') }}</label>
            <select name="sort" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                <option value="pt_no" {{ $sort === 'pt_no' ? 'selected' : '' }}>{{ __('Patient No.') }}</option>
                <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>{{ __('Name') }}</option>
                <option value="datereg" {{ $sort === 'datereg' ? 'selected' : '' }}>{{ __('Registration date') }}</option>
                <option value="tel" {{ $sort === 'tel' ? 'selected' : '' }}>{{ __('Telephone') }}</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Direction') }}</label>
            <select name="direction" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                <option value="asc" {{ $direction === 'asc' ? 'selected' : '' }}>{{ __('Asc') }} ↑</option>
                <option value="desc" {{ $direction === 'desc' ? 'selected' : '' }}>{{ __('Desc') }} ↓</option>
            </select>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Local Doctor') }}</label>
            <select name="clinic_no" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                <option value="">{{ __('All doctors') }}</option>
                @foreach ($doctors as $doc)
                    <option value="{{ $doc->Clinic_No }}" {{ request('clinic_no') == $doc->Clinic_No ? 'selected' : '' }}>{{ $doc->full_name }} ({{ $doc->Clinic_No }})</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2 pb-0.5">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">{{ __('Apply') }}</button>
            <a href="{{ route('patients.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Clear') }}</a>
        </div>
        <p class="w-full text-xs text-slate-400">{{ __('Sorting via backend query') }}: <span class="font-semibold text-slate-600">{{ match($sort){ 'name'=>'Name', 'pt_no'=>'Patient No.', 'datereg'=>'Registration date', 'tel'=>'Telephone', default=>$sort } }} ({{ $direction }})</span> — {{ __('default: newest first (Patient No. desc)') }}</p>
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