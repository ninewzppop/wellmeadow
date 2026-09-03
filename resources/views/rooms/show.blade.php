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

        {{-- Selected date (chosen on the board) + add to queue + report --}}
        <div class="flex flex-wrap items-stretch gap-3">
            <div class="flex flex-col justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-sm">
                <span class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ __('Queue date') }}</span>
                <span class="text-sm font-semibold text-slate-800">{{ $date->format('d/m/Y') }}</span>
                <a href="{{ route('rooms.index', ['date' => $date->toDateString()]) }}"
                   class="mt-0.5 text-xs font-medium text-blue-600 hover:underline">
                    {{ __('Change date') }}
                </a>
            </div>
            <x-report-button :href="route('reports.consult', ['room' => $room->Room_No, 'date' => $date->toDateString()])" />
            <a href="{{ route('appointments.create', ['room' => $room->Room_No, 'date' => $date->toDateString()]) }}"
               class="flex items-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                <span aria-hidden="true">+</span> {{ __('Add to queue') }}
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif

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
                                        <span class="relative mr-1.5 inline-flex h-2 w-2">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-500 opacity-75"></span>
                                            <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-600"></span>
                                        </span>
                                    @endif
                                    <x-status-badge :status="$appt->status" />
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @if ($appt->status === 'waiting list' || $appt->status === 'scheduled')
                                            <div class="flex flex-col items-end gap-1">
                                                <form method="POST" action="{{ route('rooms.start', [$room, $appt]) }}">
                                                    @csrf
                                                    <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                                    <button type="submit" @disabled($startBlocked)
                                                            title="{{ $startBlocked ? __('Another patient is currently in consultation in this room. Complete that visit first.') : __('Start consultation') }}"
                                                            class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white enabled:hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-40">
                                                        {{ __('Start consultation') }}
                                                    </button>
                                                </form>
                                                @if ($startBlocked)
                                                    <span class="max-w-[180px] text-right text-[10px] leading-tight font-medium text-amber-600">
                                                        {{ __('Complete the current consultation first.') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @elseif ($isBusyNow)
                                            <button type="button"
                                                    onclick="document.getElementById('med-modal-{{ $appt->Appt_No }}').showModal()"
                                                    @disabled(! $appt->patient)
                                                    title="{{ $appt->patient ? __('Order medication') : __('This appointment has no linked patient.') }}"
                                                    class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white enabled:hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40">
                                                {{ __('Order medication') }}
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
            $historyMeds = $appt->patient?->medications->sortByDesc('StartDate')->take(20) ?? collect();
        @endphp

        {{-- a) Order medication (multi-drug) --}}
        <dialog id="med-modal-{{ $appt->Appt_No }}"
                data-order-modal
                data-allergy-drugs="{{ json_encode($allergyDrugNos) }}"
                data-allergy-names="{{ json_encode($allergyNames->map(fn ($n) => mb_strtolower($n))) }}"
                class="w-full max-w-4xl rounded-2xl p-0 backdrop:bg-slate-900/50">
            <form method="POST" action="{{ route('rooms.medication-order.store', [$room, $appt]) }}" class="flex max-h-[90vh] flex-col">
                @csrf
                <input type="hidden" name="date" value="{{ $date->toDateString() }}">

                <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-6 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ __('Order medication') }}</h3>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $appt->patient?->full_name }} · {{ __('HN') }} {{ $appt->patient?->Pt_No }}
                        </p>
                    </div>
                    <button type="submit" formmethod="dialog" formnovalidate
                            class="rounded p-1 text-xl leading-none text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                            aria-label="{{ __('Close') }}">&times;</button>
                </div>

                <div class="grid flex-1 gap-0 overflow-hidden lg:grid-cols-5">
                    {{-- Left: medication history --}}
                    <div class="flex flex-col overflow-hidden border-b bg-slate-50 lg:col-span-2 lg:border-b-0 lg:border-r">
                        <div class="border-b border-slate-200 px-4 py-3">
                            <h4 class="text-xs font-bold uppercase tracking-wide text-slate-600">{{ __('Medication history') }}</h4>
                            <p class="mt-0.5 text-[11px] text-slate-400">{{ __('Tick to copy into the order on the right. You can edit before sending.') }}</p>
                        </div>
                        <div class="flex-1 overflow-y-auto p-3">
                            @if ($historyMeds->isEmpty())
                                <p class="py-8 text-center text-xs text-slate-400">{{ __('No medication history for this patient.') }}</p>
                            @else
                                <div class="space-y-2">
                                    @foreach ($historyMeds as $hm)
                                        <label class="flex cursor-pointer items-start gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 hover:border-blue-200 hover:bg-blue-50/50">
                                            <input type="checkbox" class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-blue-600"
                                                   data-history-check
                                                   data-drug-no="{{ $hm->Drug_No }}"
                                                   data-drug-name="{{ $hm->drug?->Name ?? '' }}"
                                                   data-units="{{ $hm->UnitsPerDay }}"
                                                   data-method="{{ $hm->AdminMethod }}"
                                                   data-start="{{ $hm->StartDate?->format('Y-m-d') }}"
                                                   data-finish="{{ $hm->FinishDate?->format('Y-m-d') }}">
                                            <span class="min-w-0 flex-1">
                                                <span class="block text-xs font-semibold text-slate-800">{{ $hm->drug?->Name ?? $hm->Drug_No }}</span>
                                                <span class="block text-[11px] text-slate-500">{{ $hm->UnitsPerDay }}/{{ __('day') }} · {{ $hm->AdminMethod }} · {{ $hm->StartDate?->format('d/m/Y') }} → {{ $hm->FinishDate?->format('d/m/Y') }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Right: order rows --}}
                    <div class="flex flex-col overflow-hidden lg:col-span-3">
                        <div class="flex-1 overflow-y-auto p-4">
                            {{-- Allergy banner (always visible) --}}
                            @if ($appt->patient && $appt->patient->allergies->isNotEmpty())
                                <div class="mb-4 rounded-xl border-2 border-red-200 bg-red-50 p-3">
                                    <p class="flex items-center gap-1.5 text-xs font-bold text-red-700">⚠️ {{ __('Allergies recorded for this patient') }}</p>
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        @foreach ($appt->patient->allergies as $al)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-red-600 px-3 py-1 text-xs font-semibold text-white">
                                                {{ $al->Allergy_Name ?: $al->drug?->Name ?: $al->Drug_No }}
                                                @if ($al->Severity)
                                                    <span class="rounded-full bg-white/20 px-1.5 py-0.5 text-[10px]">{{ $al->Severity }}</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                    <p class="mt-1.5 text-[11px] text-red-600">{{ __('Review before prescribing. Selecting a matching drug will require confirmation.') }}</p>
                                </div>
                            @else
                                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                                    <p class="text-xs font-semibold text-emerald-700">✓ {{ __('No allergy history') }}</p>
                                    <p class="mt-0.5 text-[11px] text-emerald-600">{{ __('No recorded allergies for this patient — checked.') }}</p>
                                </div>
                            @endif

                            {{-- Apply same dates --}}
                            <label class="mb-3 flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-2">
                                <input type="checkbox" data-apply-same-dates class="h-4 w-4 rounded border-blue-300 text-blue-600">
                                <span class="text-xs font-medium text-blue-800">{{ __('Apply same dates to all drugs') }}</span>
                            </label>
                            <div data-sync-dates-row class="mb-4 hidden grid grid-cols-2 gap-3 rounded-xl border border-blue-200 bg-blue-50/50 p-3">
                                <div>
                                    <label class="mb-1 block text-[11px] font-medium text-blue-700">{{ __('Start date') }} *</label>
                                    <input type="date" data-sync-start value="{{ now()->toDateString() }}"
                                           class="w-full rounded-lg border border-blue-200 px-2 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-medium text-blue-700">{{ __('Finish date') }} *</label>
                                    <input type="date" data-sync-finish
                                           class="w-full rounded-lg border border-blue-200 px-2 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                                </div>
                            </div>

                            <div data-allergy-warning class="mb-4 hidden rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-800">
                                <p class="font-bold">⚠️ {{ __('Allergy warning') }}</p>
                                <p class="mt-1">{{ __('The selected drug matches a recorded allergy for this patient (:list). Confirm below to dispense anyway.', ['list' => $allergenSummary]) }}</p>
                                <label class="mt-2 flex items-center gap-2 font-medium">
                                    <input type="checkbox" name="override_allergy" value="1" data-allergy-override
                                           class="h-4 w-4 rounded border-red-300 text-red-600 focus:ring-red-500">
                                    {{ __('I confirm: dispense despite recorded allergy') }}
                                </label>
                            </div>

                            <div data-rows class="space-y-3"></div>

                            <button type="button" data-add-row
                                    class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-xl border border-dashed border-slate-300 py-2.5 text-xs font-semibold text-slate-600 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                                <span class="text-sm">+</span> {{ __('Add another drug') }}
                            </button>

                            <div data-summary class="mt-4 hidden rounded-xl bg-slate-900 px-4 py-3 text-xs text-white">
                                <p class="font-semibold">{{ __('Order summary') }}</p>
                                <ul data-summary-list class="mt-1.5 list-inside list-disc space-y-0.5 text-slate-300"></ul>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 border-t border-slate-100 p-4">
                            <button type="submit" formmethod="dialog" formnovalidate
                                    class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" data-allergy-submit
                                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-40">
                                {{ __('Send to dispensing queue') }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Row template --}}
                <template data-row-template>
                    <div data-row class="grid gap-2 rounded-xl border border-slate-200 bg-white p-3 sm:grid-cols-12">
                        <div class="sm:col-span-5">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">{{ __('Drug') }} *</label>
                            <select name="drugs[__INDEX__][Drug_No]" required data-row-drug
                                    class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                                <option value="">—</option>
                                @foreach ($drugs as $drug)
                                    <option value="{{ $drug->Drug_No }}" data-name="{{ $drug->Name }}">{{ $drug->Name }} ({{ $drug->Drug_No }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">{{ __('Units per day') }} *</label>
                            <input type="number" name="drugs[__INDEX__][UnitsPerDay]" min="1" step="1" required
                                   class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">{{ __('Administration method') }} *</label>
                            <input type="text" name="drugs[__INDEX__][AdminMethod]" maxlength="30" required placeholder="{{ __('e.g. Oral, IV, IM') }}"
                                   class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        <div class="flex items-end gap-1 sm:col-span-2">
                            <button type="button" data-remove-row title="{{ __('Remove') }}"
                                    class="ml-auto rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                            </button>
                        </div>
                        <div class="sm:col-span-6">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">{{ __('Start date') }} *</label>
                            <input type="date" name="drugs[__INDEX__][StartDate]" value="{{ now()->toDateString() }}" required data-row-start
                                   class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        <div class="sm:col-span-6">
                            <label class="mb-1 block text-[11px] font-medium text-slate-500">{{ __('Finish date') }} *</label>
                            <input type="date" name="drugs[__INDEX__][FinishDate]" value="{{ now()->toDateString() }}" required data-row-finish
                                   class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                </template>
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
        document.querySelectorAll('dialog[data-order-modal]').forEach(function (dialog) {
            var form = dialog.querySelector('form');
            var rowsWrap = form.querySelector('[data-rows]');
            var tmpl = form.querySelector('[data-row-template]');
            var addBtn = form.querySelector('[data-add-row]');
            var applyCheck = form.querySelector('[data-apply-same-dates]');
            var syncRow = form.querySelector('[data-sync-dates-row]');
            var syncStart = form.querySelector('[data-sync-start]');
            var syncFinish = form.querySelector('[data-sync-finish]');
            var warning = form.querySelector('[data-allergy-warning]');
            var overrideBox = form.querySelector('[data-allergy-override]');
            var submit = form.querySelector('[data-allergy-submit]');
            var summary = form.querySelector('[data-summary]');
            var summaryList = form.querySelector('[data-summary-list]');

            var conflictDrugs = JSON.parse(dialog.dataset.allergyDrugs || '[]');
            var conflictNames = JSON.parse(dialog.dataset.allergyNames || '[]');

            function reindex() {
                rowsWrap.querySelectorAll('[data-row]').forEach(function (row, idx) {
                    row.querySelectorAll('select, input').forEach(function (el) {
                        var name = el.getAttribute('name');
                        if (name) el.setAttribute('name', name.replace(/drugs\[\d+\]/, 'drugs['+idx+']').replace('__INDEX__', idx));
                    });
                });
            }

            function addRow(prefill) {
                var frag = tmpl.content.cloneNode(true);
                var html = frag.firstElementChild.outerHTML.replace(/__INDEX__/g, rowsWrap.children.length);
                var temp = document.createElement('div');
                temp.innerHTML = html;
                var newRow = temp.firstElementChild;
                rowsWrap.appendChild(newRow);
                if (prefill) {
                    var sel = newRow.querySelector('[data-row-drug]');
                    if (prefill.Drug_No) sel.value = prefill.Drug_No;
                    newRow.querySelector('[data-row-start]').closest('[data-row]').querySelectorAll('input').forEach(function(i){});
                    // set by data attributes
                    var unitsInput = newRow.querySelector('input[name*=\"[UnitsPerDay]\"]');
                    if (unitsInput) unitsInput.value = prefill.UnitsPerDay || '';
                    var methodInput = newRow.querySelector('input[name*=\"[AdminMethod]\"]');
                    if (methodInput) methodInput.value = prefill.AdminMethod || '';
                    if (prefill.StartDate) newRow.querySelector('[data-row-start]').value = prefill.StartDate;
                    if (prefill.FinishDate) newRow.querySelector('[data-row-finish]').value = prefill.FinishDate;
                }
                bindRow(newRow);
                reindex();
                refresh();
            }

            function bindRow(row) {
                row.querySelector('[data-remove-row]').addEventListener('click', function () {
                    row.remove();
                    if (rowsWrap.children.length === 0) addRow();
                    reindex();
                    refresh();
                });
                row.querySelectorAll('select, input').forEach(function (el) {
                    el.addEventListener('change', refresh);
                    el.addEventListener('input', refresh);
                });
            }

            function refresh() {
                // sync dates
                if (applyCheck.checked) {
                    var s = syncStart.value, f = syncFinish.value;
                    rowsWrap.querySelectorAll('[data-row-start]').forEach(function (el) { if (s) el.value = s; });
                    rowsWrap.querySelectorAll('[data-row-finish]').forEach(function (el) { if (f) el.value = f; });
                }
                // allergy - highlight rows and show warning
                var hasConflict = false;
                rowsWrap.querySelectorAll('[data-row]').forEach(function (row) {
                    var sel = row.querySelector('[data-row-drug]');
                    if (!sel) return;
                    var opt = sel.selectedOptions[0];
                    var drugNo = sel.value;
                    var drugName = opt ? (opt.dataset.name || '').trim().toLowerCase() : '';
                    var isConflict = drugNo !== '' && (conflictDrugs.indexOf(drugNo) !== -1 || conflictNames.indexOf(drugName) !== -1);
                    if (isConflict) hasConflict = true;
                    row.classList.toggle('border-red-400', isConflict);
                    row.classList.toggle('bg-red-50', isConflict);
                });
                warning.classList.toggle('hidden', !hasConflict);
                if (overrideBox) {
                    overrideBox.disabled = !hasConflict;
                    submit.disabled = hasConflict && !overrideBox.checked;
                }
                // summary
                var items = [];
                rowsWrap.querySelectorAll('[data-row]').forEach(function (row) {
                    var sel = row.querySelector('[data-row-drug]');
                    var opt = sel.selectedOptions[0];
                    var name = opt && opt.value ? (opt.dataset.name || opt.textContent.trim()) : null;
                    if (name) items.push(name);
                });
                if (items.length) {
                    summary.classList.remove('hidden');
                    summaryList.innerHTML = items.map(function (n) { return '<li>' + n + '</li>'; }).join('');
                } else {
                    summary.classList.add('hidden');
                }
            }

            // apply-same-dates toggle
            applyCheck.addEventListener('change', function () {
                syncRow.classList.toggle('hidden', !this.checked);
                refresh();
            });
            if (syncStart) syncStart.addEventListener('change', refresh);
            if (syncFinish) syncFinish.addEventListener('change', refresh);
            if (overrideBox) overrideBox.addEventListener('change', refresh);

            // history checkboxes
            dialog.querySelectorAll('[data-history-check]').forEach(function (cb) {
                cb.addEventListener('change', function () {
                    if (this.checked) {
                        addRow({
                            Drug_No: this.dataset.drugNo,
                            UnitsPerDay: this.dataset.units,
                            AdminMethod: this.dataset.method,
                            StartDate: this.dataset.start,
                            FinishDate: this.dataset.finish
                        });
                        this.checked = false;
                    }
                });
            });

            // strip empty rows before submit so they don't trigger required validation
            form.addEventListener('submit', function () {
                reindex();
                var rows = rowsWrap.querySelectorAll('[data-row]');
                var hasValid = false;
                rows.forEach(function(r){ if (r.querySelector('[data-row-drug]').value) hasValid = true; });
                if (!hasValid) return;
                rows.forEach(function(r){
                    if (!r.querySelector('[data-row-drug]').value) {
                        r.querySelectorAll('select, input').forEach(function(el){
                            el.removeAttribute('name');
                            el.removeAttribute('required');
                        });
                        r.style.display = 'none';
                    }
                });
                reindex();
            });

            // init
            addRow();
            if (applyCheck) syncRow.classList.toggle('hidden', !applyCheck.checked);
            addBtn.addEventListener('click', function () { addRow(); });

            // re-init on dialog open (ensure one row)
            dialog.addEventListener('close', function () {
                // keep rows for next open; do not clear
            });
        });
    </script>
@endpush
