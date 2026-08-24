@extends('layouts.app')

@section('title', __('In-Patients'))

@section('content')
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('In-Patients') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Manage inpatient admissions and bed assignments') }}
            </p>
        </div>
        <a href="{{ route('in-patients.create') }}"
           class="flex items-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
            <span aria-hidden="true">+</span> {{ __('New Admission') }}
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="min-w-[200px] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
            <input type="search" name="search" value="{{ request('search') }}"
                   placeholder="{{ __('Admission No., Patient...') }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        </div>

        <div class="grid min-w-[280px] grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Date From') }}</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Date To') }}</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>
        </div>

        <div class="grid min-w-[280px] grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Status') }}</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                            {{ match($status) {
                                'current' => __('Currently Admitted'),
                                'discharged' => __('Discharged'),
                                'waiting' => __('Waiting List'),
                                default => ucfirst($status),
                            } }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Ward') }}</label>
                <select name="ward" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($wards as $ward)
                        <option value="{{ $ward->Wd_No }}" {{ request('ward') == $ward->Wd_No ? 'selected' : '' }}>
                            {{ $ward->Wd_Name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">{{ __('Filter') }}</button>
            <a href="{{ route('in-patients.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Clear') }}</a>
        </div>
    </form>

    @if ($inPatients->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-400 shadow-sm">
            {{ __('No admissions found.') }}
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-6 py-4">{{ __('Admission No.') }}</th>
                            <th class="px-6 py-4">{{ __('Patient') }}</th>
                            <th class="px-6 py-4">{{ __('Ward / Bed') }}</th>
                            <th class="px-6 py-4">{{ __('Admitted') }}</th>
                            <th class="px-6 py-4">{{ __('Expected Leave') }}</th>
                            <th class="px-6 py-4">{{ __('Actual Leave') }}</th>
                            <th class="px-6 py-4">{{ __('Status') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($inPatients as $ip)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-slate-700">{{ $ip->In_Pt_No }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    {{ $ip->patient->full_name ?? '—' }}<br>
                                    <span class="text-xs text-slate-400">{{ $ip->patient->Pt_No ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $ip->bed->ward->Wd_Name ?? '—' }} / {{ $ip->bed->Bed_No ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $ip->DatePlaced?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $ip->DateLeave?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $ip->ActDateLeft?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusBadge = $ip->ActDateLeft ? 'bg-emerald-100 text-emerald-700' : ($ip->DatePlaced ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700');
                                        $statusText = $ip->ActDateLeft ? __('Discharged') : ($ip->DatePlaced ? __('Admitted') : __('Waiting'));
                                    @endphp
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('in-patients.show', $ip) }}"
                                           title="{{ __('View') }}"
                                           class="text-slate-400 hover:text-slate-700">
                                            &#128065;
                                        </a>
                                        <a href="{{ route('in-patients.edit', $ip) }}"
                                           title="{{ __('Edit') }}"
                                           class="text-slate-400 hover:text-slate-700">
                                            &#9998;
                                        </a>
                                        <form method="POST" action="{{ route('in-patients.destroy', $ip) }}"
                                              onsubmit="return confirm('{{ __('Delete this admission?') }}')">
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

            <div class="mt-4 flex justify-center">
                {{ $inPatients->links() }}
            </div>
        </div>
    @endif
@endsection