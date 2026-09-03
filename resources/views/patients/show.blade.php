@extends('layouts.app')

@section('title', $patient->full_name)

@section('content')
<div class="mx-auto max-w-5xl">
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $patient->full_name }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Patient No.') }}: {{ $patient->Pt_No }}</p>
        </div>
        <div class="flex gap-2">
            <x-report-button :href="route('reports.patient', $patient)" />
            <a href="{{ route('patients.edit', $patient) }}"
               class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                {{ __('Edit') }}
            </a>
            <a href="{{ route('patients.index') }}"
               class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                {{ __('Back to List') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Basic information --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Basic Information') }}</h2>
                <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Patient No.') }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $patient->Pt_No }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Name') }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $patient->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Date of Birth') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $patient->DOB ? $patient->DOB->format('d/m/Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Sex') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $patient->Sex ? ($patient->Sex === 'M' ? __('Male') : __('Female')) : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Phone') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $patient->TelNo ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Address') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $patient->Address ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Marital Status') }}</dt>
                        <dd class="mt-0.5">
                            @if ($patient->MaritalStat)
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    {{ $patient->MaritalStat }}
                                </span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Date Registered') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $patient->DateReg ? $patient->DateReg->format('d/m/Y') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Primary Care Doctor') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $patient->localDoctor?->full_name ?? '-' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Next of kin --}}
            @if ($patient->nextOfKins->isNotEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Next of Kin') }}</h2>
                    <div class="overflow-hidden rounded-xl border border-slate-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead class="bg-slate-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        <th class="px-4 py-3">{{ __('Name') }}</th>
                                        <th class="px-4 py-3">{{ __('Relationship') }}</th>
                                        <th class="px-4 py-3">{{ __('Phone') }}</th>
                                        <th class="px-4 py-3">{{ __('Address') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($patient->nextOfKins as $nok)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-slate-800">{{ $nok->FirstName }} {{ $nok->LastName }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ $nok->Relationship }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ $nok->TelNo }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ $nok->Address }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Allergies --}}
            @if ($patient->allergies->isNotEmpty())
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Allergies') }}</h2>
                    <div class="overflow-hidden rounded-xl border border-slate-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead class="bg-slate-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        <th class="px-4 py-3">{{ __('Allergen') }}</th>
                                        <th class="px-4 py-3">{{ __('Reaction') }}</th>
                                        <th class="px-4 py-3">{{ __('Severity') }}</th>
                                        <th class="px-4 py-3">{{ __('Diagnosed') }}</th>
                                        <th class="px-4 py-3">{{ __('Recorded By') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($patient->allergies as $allergy)
                                        @php
                                            $severityClass = match($allergy->Severity) {
                                                'Severe' => 'bg-red-100 text-red-700',
                                                'Moderate' => 'bg-amber-100 text-amber-700',
                                                default => 'bg-sky-100 text-sky-700',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-slate-800">{{ $allergy->Allergy_Name ?? $allergy->drug?->Name }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ $allergy->Reaction }}</td>
                                            <td class="px-4 py-3">
                                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $severityClass }}">
                                                    {{ $allergy->Severity }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-slate-600">{{ $allergy->DiagDate->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ $allergy->recordedBy?->full_name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Medication history --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700">{{ __('Medication History') }}</h2>
                    <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                        {{ $patient->medications->count() }} {{ __('records') }}
                    </span>
                </div>
                @if ($patient->medications->isEmpty())
                    <p class="py-6 text-center text-sm text-slate-400">{{ __('No medication history.') }}</p>
                @else
                    <div class="overflow-hidden rounded-xl border border-slate-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead class="bg-slate-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        <th class="px-4 py-3">{{ __('Drug') }}</th>
                                        <th class="px-4 py-3">{{ __('Units per day') }}</th>
                                        <th class="px-4 py-3">{{ __('Administration method') }}</th>
                                        <th class="px-4 py-3">{{ __('Period') }}</th>
                                        <th class="px-4 py-3">{{ __('Prescribed by') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($patient->medications->sortByDesc('StartDate') as $med)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <span class="font-medium text-slate-800">{{ $med->drug?->Name ?? $med->Drug_No }}</span>
                                                <span class="block text-xs text-slate-400">{{ $med->Med_No }} · {{ $med->Drug_No }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-slate-600">{{ $med->UnitsPerDay }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ $med->AdminMethod }}</td>
                                            <td class="px-4 py-3 text-slate-600">
                                                {{ $med->StartDate?->format('d/m/Y') }} → {{ $med->FinishDate?->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-slate-600">{{ $med->staff?->full_name ?? $med->Stf_No ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </section>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Appointments --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700">{{ __('Appointments') }}</h2>
                    <a href="{{ route('appointments.index') }}?patient={{ $patient->Pt_No }}"
                       class="text-xs font-medium text-slate-500 hover:text-slate-800">
                        {{ __('View All') }}
                    </a>
                </div>
                @if ($patient->appointments->isEmpty())
                    <p class="text-sm text-slate-400">{{ __('No appointments') }}</p>
                @else
                    <div class="space-y-2">
                        @foreach ($patient->appointments->take(5) as $appt)
                            @php
                                $statusClass = match($appt->status) {
                                    'scheduled' => 'bg-sky-100 text-sky-700',
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <div class="rounded-lg bg-slate-50 p-3">
                                <div class="text-sm font-medium text-slate-800">
                                    {{ $appt->ApptDate->format('d/m/Y') }} {{ $appt->ApptTime->format('H:i') }}
                                </div>
                                <div class="mt-0.5 text-xs text-slate-500">
                                    {{ $appt->consultant?->full_name ?? '—' }} &middot; {{ $appt->room?->Room_No ?? '—' }}
                                </div>
                                <span class="mt-2 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">
                                    {{ $appt->status ?? '—' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- In-patient admissions --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('In-Patient Admissions') }}</h2>
                @if ($patient->inPatients->isEmpty())
                    <p class="text-sm text-slate-400">{{ __('No admissions') }}</p>
                @else
                    <div class="space-y-2">
                        @foreach ($patient->inPatients as $ip)
                            <div class="rounded-lg bg-slate-50 p-3">
                                <div class="text-sm font-medium text-slate-800">
                                    {{ $ip->DatePlaced?->format('d/m/Y') }} - {{ $ip->DateLeave?->format('d/m/Y') ?? __('Ongoing') }}
                                </div>
                                <div class="mt-0.5 text-xs text-slate-500">
                                    {{ $ip->bed?->Bed_No }} &middot; {{ $ip->bed?->ward?->Wd_Name }}
                                </div>
                                <div class="mt-1 text-xs font-medium text-slate-600">
                                    {{ $ip->ActDateLeft ? __('Discharged') . ' ' . $ip->ActDateLeft->format('d/m/Y') : __('Currently admitted') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection