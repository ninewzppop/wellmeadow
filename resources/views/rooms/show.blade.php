@extends('layouts.app')

@section('title', __('Room Queue'))

@section('content')
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                <a href="{{ route('rooms.index') }}" class="hover:text-blue-600">{{ __('Consultation Rooms') }}</a>
                /
                {{ $room->Room_No }}
            </p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $room->RoomName ?? __('Room') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $room->Location ?? '—' }}</p>
        </div>

        {{-- Date picker + add to queue --}}
        <div class="flex flex-wrap items-start gap-3">
            <form method="GET" action="{{ route('rooms.show', $room) }}"
                  class="flex items-end gap-2 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Queue date') }}</label>
                    <input type="date" name="date" value="{{ $date->toDateString() }}"
                           class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none">
                </div>
                <button type="submit"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    {{ __('Apply') }}
                </button>
                <a href="{{ route('rooms.show', [$room, 'date' => now()->toDateString()]) }}"
                   class="rounded-lg px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                    {{ __('Today') }}
                </a>
            </form>
            <a href="{{ route('appointments.create', ['room' => $room->Room_No, 'date' => $date->toDateString()]) }}"
               class="flex items-center gap-2 self-stretch rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                <span aria-hidden="true">+</span> {{ __('Add to queue') }}
            </a>
        </div>
    </div>

    {{-- Active queue --}}
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">
        {{ __('Patient queue') }} ({{ $queue->count() }})
    </h2>

    @if ($queue->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-400 shadow-sm">
            {{ __('No patients waiting in this room on this date.') }}
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="w-14 px-5 py-4">{{ __('#') }}</th>
                            <th class="px-5 py-4">{{ __('Appt Time') }}</th>
                            <th class="px-5 py-4">{{ __('Patient') }}</th>
                            <th class="px-5 py-4">{{ __('Consultant') }}</th>
                            <th class="px-5 py-4">{{ __('Status') }}</th>
                            <th class="px-5 py-4 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($queue as $i => $appt)
                            @php
                                $isBusyNow = $appt->status === 'in consultation';
                                $startBlocked = ! $isBusyNow && $inConsultationNo !== null;
                                $hasAllergy = $appt->patient && $appt->patient->allergies->isNotEmpty();
                                $allergenList = $hasAllergy
                                    ? $appt->patient->allergies
                                        ->map(fn ($a) => $a->Allergy_Name ?: $a->drug?->Name ?: $a->Drug_No)
                                        ->filter()
                                        ->implode(', ')
                                    : '';
                            @endphp
                            <tr class="{{ $isBusyNow ? 'bg-amber-50 ring-1 ring-inset ring-amber-200' : '' }} hover:bg-slate-50">
                                <td class="px-5 py-4 font-bold {{ $isBusyNow ? 'text-amber-700' : 'text-slate-400' }}">{{ $i + 1 }}</td>
                                <td class="px-5 py-4 font-medium text-slate-700">
                                    {{ optional($appt->ApptTime)->format('H:i') ?? '—' }}
                                    <span class="block text-xs font-normal text-slate-400">{{ $appt->Appt_No }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-900">
                                            {{ $appt->patient?->full_name ?? '—' }}
                                        </span>
                                        @if ($hasAllergy)
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                 class="h-4 w-4 shrink-0 text-red-500" role="img"
                                                 aria-label="{{ __('Allergy history') }}" title="{{ __('Allergy history') }}: {{ $allergenList }}">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <span class="text-xs text-slate-400">{{ __('HN') }} {{ $appt->patient?->Pt_No ?? '—' }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $appt->consultant?->full_name ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    @if ($isBusyNow)
                                        <span class="mr-1.5 inline-flex h-2 w-2">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-500 opacity-75"></span>
                                            <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-600"></span>
                                        </span>
                                    @endif
                                    <x-status-badge :status="$appt->status" />
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if ($appt->status === 'waiting list' || $appt->status === 'scheduled')
                                            <form method="POST" action="{{ route('rooms.start', [$room, $appt]) }}">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                                <button type="submit" @disabled($startBlocked)
                                                        title="{{ $startBlocked ? __('Another patient is currently in consultation in this room. Complete that visit first.') : __('Start consultation') }}"
                                                        class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white enabled:hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-40">
                                                    {{ __('Start consultation') }}
                                                </button>
                                            </form>
                                        @elseif ($isBusyNow)
                                            <button type="button"
                                                    onclick="document.getElementById('med-modal-{{ $appt->Appt_No }}').showModal()"
                                                    @disabled(! $appt->patient)
                                                    title="{{ $appt->patient ? __('Dispense medication and complete the visit') : __('This appointment has no linked patient.') }}"
                                                    class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white enabled:hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40">
                                                {{ __('Dispense medication') }}
                                            </button>
                                            <button type="button"
                                                    onclick="document.getElementById('admit-modal-{{ $appt->Appt_No }}').showModal()"
                                                    @disabled(! $appt->patient)
                                                    title="{{ $appt->patient ? __('Admit to in-patient waiting list and complete the visit') : __('This appointment has no linked patient.') }}"
                                                    class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white enabled:hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-40">
                                                {{ __('Admit as inpatient') }}
                                            </button>
                                            <form method="POST" action="{{ route('rooms.complete', [$room, $appt]) }}"
                                                  onsubmit="return confirm('{{ __('Complete this visit without further actions?') }}')">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                                <button type="submit"
                                                        class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                                    {{ __('Finish visit') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Finished today --}}
    @if ($finished->isNotEmpty())
        <h2 class="mb-3 mt-8 text-sm font-semibold uppercase tracking-wide text-slate-400">
            {{ __('Finished today') }} ({{ $finished->count() }})
        </h2>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white opacity-75 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/60">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-3">{{ __('Appt Time') }}</th>
                            <th class="px-5 py-3">{{ __('Patient') }}</th>
                            <th class="px-5 py-3">{{ __('Consultant') }}</th>
                            <th class="px-5 py-3">{{ __('Outcome') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($finished as $appt)
                            <tr class="text-slate-500">
                                <td class="px-5 py-3">{{ optional($appt->ApptTime)->format('H:i') ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    {{ $appt->patient?->full_name ?? '—' }}
                                    <span class="text-xs text-slate-400">{{ $appt->patient?->Pt_No }}</span>
                                </td>
                                <td class="px-5 py-3">{{ $appt->consultant?->full_name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <x-status-badge :status="$appt->status" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Modals: one per in-consultation appointment --}}
    @foreach ($queue->where('status', 'in consultation') as $appt)
        @php
            $allergyDrugNos = $appt->patient?->allergies->map(fn ($a) => $a->Drug_No)->filter()->values() ?? collect();
            $allergyNames = $appt->patient?->allergies->map(fn ($a) => $a->Allergy_Name)->filter()->values() ?? collect();
            $allergenSummary = $appt->patient?->allergies
                ->map(fn ($a) => $a->Allergy_Name ?: $a->drug?->Name)
                ->filter()
                ->implode(', ') ?? '';
        @endphp

        {{-- a) Dispense medication --}}
        <dialog id="med-modal-{{ $appt->Appt_No }}"
                data-allergy-check
                class="w-full max-w-lg rounded-2xl p-0 backdrop:bg-slate-900/50">
            <form method="POST" action="{{ route('rooms.medicate', [$room, $appt]) }}"
                  data-allergy-drugs="{{ json_encode($allergyDrugNos) }}"
                  data-allergy-names="{{ json_encode($allergyNames->map(fn ($n) => mb_strtolower($n))) }}"
                  class="p-6">
                @csrf
                <input type="hidden" name="date" value="{{ $date->toDateString() }}">

                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ __('Dispense medication') }}</h3>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $appt->patient?->full_name }} · {{ __('HN') }} {{ $appt->patient?->Pt_No }}
                        </p>
                    </div>
                    <button type="submit" formmethod="dialog" formnovalidate
                            class="rounded p-1 text-xl leading-none text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                            aria-label="{{ __('Close') }}">&times;</button>
                </div>

                <div data-allergy-warning class="mb-4 hidden rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-800">
                    <p class="font-bold">⚠️ {{ __('Allergy warning') }}</p>
                    <p class="mt-1">
                        {{ __('The selected drug matches a recorded allergy for this patient (:list). Confirm below to dispense anyway.', ['list' => $allergenSummary]) }}
                    </p>
                    <label class="mt-2 flex items-center gap-2 font-medium">
                        <input type="checkbox" name="override_allergy" value="1" data-allergy-override
                               class="h-4 w-4 rounded border-red-300 text-red-600 focus:ring-red-500">
                        {{ __('I confirm: dispense despite recorded allergy') }}
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Drug') }} *</label>
                        <select name="Drug_No" required data-allergy-drug-select
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">—</option>
                            @foreach ($drugs as $drug)
                                <option value="{{ $drug->Drug_No }}" data-name="{{ $drug->Name }}">
                                    {{ $drug->Name }} ({{ $drug->Drug_No }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Units per day') }} *</label>
                        <input type="number" name="UnitsPerDay" min="1" step="1" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Administration method') }} *</label>
                        <input type="text" name="AdminMethod" maxlength="30" required placeholder="{{ __('e.g. Oral, IV, IM') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Start date') }} *</label>
                        <input type="date" name="StartDate" value="{{ now()->toDateString() }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Finish date') }} *</label>
                        <input type="date" name="FinishDate" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="submit" formmethod="dialog" formnovalidate
                            class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" data-allergy-submit
                            class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40">
                        {{ __('Save & complete visit') }}
                    </button>
                </div>
            </form>
        </dialog>

        {{-- b) Admit as inpatient --}}
        <dialog id="admit-modal-{{ $appt->Appt_No }}"
                class="w-full max-w-md rounded-2xl p-0 backdrop:bg-slate-900/50">
            <form method="POST" action="{{ route('rooms.admit', [$room, $appt]) }}" class="p-6">
                @csrf
                <input type="hidden" name="date" value="{{ $date->toDateString() }}">

                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ __('Admit as inpatient') }}</h3>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $appt->patient?->full_name }} · {{ __('HN') }} {{ $appt->patient?->Pt_No }}
                        </p>
                    </div>
                    <button type="submit" formmethod="dialog" formnovalidate
                            class="rounded p-1 text-xl leading-none text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                            aria-label="{{ __('Close') }}">&times;</button>
                </div>

                <p class="mb-4 rounded-xl bg-violet-50 p-3 text-xs text-violet-800">
                    {{ __('The patient will be added to the in-patient waiting list dated today (:date). A ward and bed are assigned later from the In-patients page.', ['date' => $date->format('d/m/Y')]) }}
                </p>

                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Expected duration of stay (days)') }} *</label>
                    <input type="number" name="ExpStayDays" min="1" step="1" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="submit" formmethod="dialog" formnovalidate
                            class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-violet-600 px-5 py-2 text-sm font-semibold text-white hover:bg-violet-700">
                        {{ __('Add to waiting list & complete') }}
                    </button>
                </div>
            </form>
        </dialog>
    @endforeach
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('dialog[data-allergy-check]').forEach(function (dialog) {
            var form = dialog.querySelector('form');
            var select = form.querySelector('[data-allergy-drug-select]');
            var warning = form.querySelector('[data-allergy-warning]');
            var overrideBox = form.querySelector('[data-allergy-override]');
            var submit = form.querySelector('[data-allergy-submit]');

            var conflictDrugs = JSON.parse(form.dataset.allergyDrugs || '[]');
            var conflictNames = JSON.parse(form.dataset.allergyNames || '[]');

            function update() {
                var option = select.selectedOptions[0];
                var drugNo = select.value;
                var drugName = option ? (option.dataset.name || '').trim().toLowerCase() : '';
                var conflict = drugNo !== '' && (
                    conflictDrugs.indexOf(drugNo) !== -1 ||
                    conflictNames.indexOf(drugName) !== -1
                );

                warning.classList.toggle('hidden', !conflict);
                overrideBox.disabled = !conflict;
                submit.disabled = conflict && !overrideBox.checked;
            }

            select.addEventListener('change', update);
            if (overrideBox) {
                overrideBox.addEventListener('change', update);
            }
            update();
        });
    </script>
@endpush
