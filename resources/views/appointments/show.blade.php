@extends('layouts.app')

@section('title', 'Appt. ' . $appointment->Appt_No)

@section('content')
<div class="mx-auto max-w-4xl">
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Appt. {{ $appointment->Appt_No }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Status') }}: 
                <span class="badge ml-2 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ match($appointment->status) {
                    'scheduled' => 'bg-sky-100 text-sky-700',
                    'completed' => 'bg-emerald-100 text-emerald-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                    'no-show' => 'bg-amber-100 text-amber-700',
                    default => 'bg-slate-100 text-slate-600',
                } }}">
                    {{ ucfirst(str_replace('-', ' ', $appointment->status)) }}
                </span>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('appointments.edit', $appointment) }}"
               class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                {{ __('Edit') }}
            </a>
            <a href="{{ route('appointments.index') }}"
               class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                {{ __('Back to List') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Appointment Details --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Appointment Details') }}</h2>
                <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Appointment No.') }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $appointment->Appt_No }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Date') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $appointment->ApptDate->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Time') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $appointment->ApptTime->format('H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Status') }}</dt>
                        <dd class="mt-0.5">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ match($appointment->status) {
                                'scheduled' => 'bg-sky-100 text-sky-700',
                                'completed' => 'bg-emerald-100 text-emerald-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                'no-show' => 'bg-amber-100 text-amber-700',
                                default => 'bg-slate-100 text-slate-600',
                            } }}">
                                {{ ucfirst(str_replace('-', ' ', $appointment->status)) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Patient --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Patient') }}</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Name') }}</dt>
                        <dd class="mt-0.5 font-semibold text-slate-900"><x-patient-link :patient="$appointment->patient" /></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Patient No.') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $appointment->patient->Pt_No ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('DOB') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $appointment->patient->DOB ? $appointment->patient->DOB->format('d/m/Y') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Phone') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $appointment->patient->TelNo ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Sex') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $appointment->patient->Sex ? ($appointment->patient->Sex === 'M' ? __('Male') : __('Female')) : '—' }}</dd>
                    </div>
                </dl>
                @if ($appointment->patient)
                    <a href="{{ route('patients.show', $appointment->patient) }}" class="mt-4 inline-block text-sm font-medium text-slate-500 hover:text-slate-800">
                        {{ __('View Patient Details') }}
                    </a>
                @endif
            </section>

            {{-- Doctor --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Doctor') }}</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Name') }}</dt>
                        <dd class="mt-0.5 font-semibold text-slate-900">{{ $appointment->consultant->full_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Staff No.') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $appointment->consultant->Stf_No ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Room --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Room') }}</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Name') }}</dt>
                        <dd class="mt-0.5 font-semibold text-slate-900">{{ $appointment->room->RoomName ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Room No.') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $appointment->room->Room_No ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('Location') }}</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $appointment->room->Location ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Outpatient --}}
            @if ($appointment->outpatient)
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Outpatient') }}</h2>
                    <p class="text-sm text-slate-600">{{ __('This appointment is marked as outpatient.') }}</p>
                </section>
            @endif
        </div>

        {{-- Sidebar - Related Info --}}
        <div class="space-y-6">
            {{-- Patient's Other Appointments --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700">{{ __('Patient\'s Other Appointments') }}</h2>
                    <a href="{{ route('appointments.index') }}?patient={{ $appointment->Pt_No }}"
                       class="text-xs font-medium text-slate-500 hover:text-slate-800">
                        {{ __('View All') }}
                    </a>
                </div>
                @if ($appointment->patient && $appointment->patient->appointments->count() > 1)
                    <div class="space-y-2">
                        @foreach ($appointment->patient->appointments->reject(fn($a) => $a->Appt_No === $appointment->Appt_No)->take(5) as $appt)
                            @php
                                $statusClass = match($appt->status) {
                                    'scheduled' => 'bg-sky-100 text-sky-700',
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    'no-show' => 'bg-amber-100 text-amber-700',
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
                                    {{ ucfirst(str_replace('-', ' ', $appt->status ?? '—')) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400">{{ __('No other appointments') }}</p>
                @endif
            </section>

            {{-- Doctor's Schedule (same day) --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Doctor\'s Schedule (Same Day)') }}</h2>
                @php
                    $doctorAppointments = $appointment->consultant?->appointments()
                        ->where('ApptDate', $appointment->ApptDate)
                        ->where('Appt_No', '!=', $appointment->Appt_No)
                        ->with(['patient', 'room'])
                        ->orderBy('ApptTime')
                        ->get();
                @endphp
                @if ($doctorAppointments && $doctorAppointments->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($doctorAppointments as $appt)
                            @php
                                $statusClass = match($appt->status) {
                                    'scheduled' => 'bg-sky-100 text-sky-700',
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    'no-show' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <div class="rounded-lg bg-slate-50 p-3">
                                <div class="text-sm font-medium text-slate-800">
                                    {{ $appt->ApptTime->format('H:i') }} &middot; <x-patient-link :patient="$appt->patient" />
                                </div>
                                <div class="mt-0.5 text-xs text-slate-500">
                                    Room: {{ $appt->room->RoomName ?? '—' }}
                                </div>
                                <span class="mt-2 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst(str_replace('-', ' ', $appt->status ?? '—')) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400">{{ __('No other appointments this day') }}</p>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection