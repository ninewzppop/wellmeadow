@extends('layouts.app')

@section('title', 'Adm. ' . $inPatient->In_Pt_No)

@section('content')
<div class="mx-auto max-w-4xl">
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Adm. {{ $inPatient->In_Pt_No }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                @php
                    $statusBadge = $inPatient->ActDateLeft ? 'bg-emerald-100 text-emerald-700' : ($inPatient->DatePlaced ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700');
                    $statusText = $inPatient->ActDateLeft ? __('Discharged') : ($inPatient->DatePlaced ? __('Admitted') : __('Waiting'));
                @endphp
                {{ __('Status') }}: 
                <span class="badge ml-2 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                    {{ $statusText }}
                </span>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('in-patients.edit', $inPatient) }}"
               class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                {{ __('Edit') }}
            </a>
            <a href="{{ route('in-patients.index') }}"
               class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                {{ __('Back to List') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Admission Details --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Admission Details') }}</h2>
                <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Admission No.') }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $inPatient->In_Pt_No }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Wait List Date') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $inPatient->DateWaitList?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Expected Stay (Days)') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $inPatient->ExpStayDays ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Admission Date') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $inPatient->DatePlaced?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Expected Leave Date') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $inPatient->DateLeave?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Actual Leave Date') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $inPatient->ActDateLeft?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Patient --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Patient') }}</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Name') }}</dt>
                        <dd class="mt-0.5 font-semibold text-slate-900"><x-patient-link :patient="$inPatient->patient" /></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Patient No.') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $inPatient->patient->Pt_No ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('DOB') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $inPatient->patient->DOB ? $inPatient->patient->DOB->format('d/m/Y') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Sex') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $inPatient->patient->Sex ? ($inPatient->patient->Sex === 'M' ? __('Male') : __('Female')) : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Phone') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $inPatient->patient->TelNo ?? '—' }}</dd>
                    </div>
                </dl>
                @if ($inPatient->patient)
                    <a href="{{ route('patients.show', $inPatient->patient) }}" class="mt-4 inline-block text-sm font-medium text-slate-500 hover:text-slate-800">
                        {{ __('View Patient Details') }}
                    </a>
                @endif
            </section>

            {{-- Bed & Ward --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Bed & Ward') }}</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Ward') }}</dt>
                        <dd class="mt-0.5 font-semibold text-slate-900">{{ $inPatient->bed->ward->Wd_Name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Ward Location') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $inPatient->bed->ward->Location ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Bed No.') }}</dt>
                        <dd class="mt-0.5 font-semibold text-slate-900">{{ $inPatient->bed->Bed_No ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Bed Status') }}</dt>
                        <dd class="mt-0.5">
                            @php
                                $bedStatusBadge = match($inPatient->bed->BedStatus ?? 'Available') {
                                    'Occupied' => 'bg-sky-100 text-sky-700',
                                    'Available' => 'bg-emerald-100 text-emerald-700',
                                    'Maintenance' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $bedStatusBadge }}">
                                {{ $inPatient->bed->BedStatus ?? 'Available' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Patient's Other Admissions --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700">{{ __('Patient\'s Admission History') }}</h2>
                    <a href="{{ route('in-patients.index') }}?search={{ $inPatient->Pt_No }}"
                       class="text-xs font-medium text-slate-500 hover:text-slate-800">
                        {{ __('View All') }}
                    </a>
                </div>
                @php
                    $otherAdmissions = $inPatient->patient?->inPatients()
                        ->where('In_Pt_No', '!=', $inPatient->In_Pt_No)
                        ->with('bed.ward')
                        ->orderBy('DatePlaced', 'desc')
                        ->take(5)
                        ->get();
                @endphp
                @if ($otherAdmissions && $otherAdmissions->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($otherAdmissions as $ip)
                            @php
                                $statusBadge = $ip->ActDateLeft ? 'bg-emerald-100 text-emerald-700' : ($ip->DatePlaced ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700');
                                $statusText = $ip->ActDateLeft ? __('Discharged') : ($ip->DatePlaced ? __('Admitted') : __('Waiting'));
                            @endphp
                            <div class="rounded-lg bg-slate-50 p-3">
                                <div class="text-sm font-medium text-slate-800">
                                    {{ $ip->In_Pt_No }} &middot; {{ $ip->bed->ward->Wd_Name ?? '—' }} / {{ $ip->bed->Bed_No ?? '—' }}
                                </div>
                                <div class="mt-0.5 text-xs text-slate-500">
                                    {{ $ip->DatePlaced?->format('d/m/Y') }} - {{ $ip->DateLeave?->format('d/m/Y') ?? __('Ongoing') }}
                                </div>
                                <span class="mt-2 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge }}">
                                    {{ $statusText }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400">{{ __('No other admissions') }}</p>
                @endif
            </section>

            {{-- Ward Occupancy --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Ward Occupancy') }}</h2>
                @php
                    $ward = $inPatient->bed->ward;
                    $totalBeds = $ward ? \App\Models\Bed::where('Wd_No', $ward->Wd_No)->count() : 0;
                    $occupiedBeds = $ward ? \App\Models\Bed::where('Wd_No', $ward->Wd_No)->where('BedStatus', 'Occupied')->count() : 0;
                    $availableBeds = $ward ? \App\Models\Bed::where('Wd_No', $ward->Wd_No)->where('BedStatus', 'Available')->count() : 0;
                @endphp
                @if ($ward)
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">{{ __('Ward') }}</dt>
                            <dd class="font-medium">{{ $ward->Wd_Name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">{{ __('Total Beds') }}</dt>
                            <dd class="font-medium">{{ $totalBeds }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">{{ __('Occupied') }}</dt>
                            <dd class="font-medium text-sky-600">{{ $occupiedBeds }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">{{ __('Available') }}</dt>
                            <dd class="font-medium text-emerald-600">{{ $availableBeds }}</dd>
                        </div>
                        <div class="flex justify-between pt-2 border-t">
                            <dt class="text-slate-500">{{ __('Occupancy Rate') }}</dt>
                            <dd class="font-medium">{{ $totalBeds > 0 ? number_format($occupiedBeds / $totalBeds * 100, 1) : 0 }}%</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-slate-400">{{ __('No ward assigned') }}</p>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection