@extends('layouts.app')

@section('title', $appointment->exists ? __('Edit Appointment') : __('New Appointment'))

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="rounded-2xl bg-white p-8 shadow-sm">
        <h1 class="mb-6 text-2xl font-bold text-slate-900">
            {{ $appointment->exists ? __('Edit Appointment') : __('New Appointment') }}
        </h1>

        <form method="POST" action="{{ $appointment->exists ? route('appointments.update', $appointment) : route('appointments.store') }}">
            @csrf
            @if ($appointment->exists)
                @method('PUT')
            @else
                <input type="hidden" name="ctx_room" value="{{ request('room') }}">
                <input type="hidden" name="ctx_date" value="{{ request('date') }}">
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                    <ul class="list-inside list-disc text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                {{-- Main appointment details --}}
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2 lg:col-span-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Appointment No.') }}</label>
                        <input type="text" value="{{ $appointment->exists ? $appointment->Appt_No : $nextApptNo }}" disabled
                               placeholder="{{ __('Auto-generated') }}"
                               class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500 cursor-not-allowed">
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $appointment->exists ? __('Cannot be changed after creation') : __('Generated automatically when saved') }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Patient') }} <span class="text-red-500">*</span>
                        </label>
                        <x-patient-select name="Pt_No" :patients="$patients" :selected="old('Pt_No', $appointment->Pt_No)" />
                        @error('Pt_No')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Doctor') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="Consult_Stf_No" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" required>
                            <option value="">{{ __('Select Doctor') }}</option>
                            @foreach ($consultants as $doctor)
                                <option value="{{ $doctor->Stf_No }}" {{ old('Consult_Stf_No', $appointment->Consult_Stf_No) == $doctor->Stf_No ? 'selected' : '' }}>
                                    {{ $doctor->full_name }} ({{ $doctor->Stf_No }})
                                </option>
                            @endforeach
                        </select>
                        @error('Consult_Stf_No')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Room') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="Room_No" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" required>
                            <option value="">{{ __('Select Room') }}</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->Room_No }}" {{ old('Room_No', $appointment->Room_No ?? request('room')) == $room->Room_No ? 'selected' : '' }}>
                                    {{ $room->RoomName }} ({{ $room->Room_No }})
                                </option>
                            @endforeach
                        </select>
                        @error('Room_No')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Status') }} <span class="text-red-500">*</span>
                        </label>
                        @php
                            $statusLocked = $appointment->exists && count($statuses) === 1;
                        @endphp
                        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-slate-100 disabled:text-slate-500"
                                required @disabled($statusLocked)>
                            @if ($statusLocked)
                                <option value="{{ $statuses[0] }}" selected>
                                    {{ \App\Models\Appointment::statusLabels()[$statuses[0]] ?? $statuses[0] }}
                                </option>
                            @else
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" {{ old('status', $appointment->status ?? 'scheduled') === $status ? 'selected' : '' }}>
                                        {{ \App\Models\Appointment::statusLabels()[$status] ?? ucfirst($status) }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @if ($statusLocked)
                            <p class="mt-1 text-xs text-slate-400">{{ __('Managed from the room queue page.') }}</p>
                        @endif
                        @error('status')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Appointment Date') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="ApptDate" value="{{ old('ApptDate', $appointment->ApptDate?->format('Y-m-d') ?? request('date')) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" required>
                        @error('ApptDate')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Appointment Time') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="ApptTime" value="{{ old('ApptTime', $appointment->ApptTime?->format('H:i')) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" required>
                        @error('ApptTime')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Allergy record (optional, same fields as New Allergy Record) --}}
                <div class="rounded-2xl bg-amber-50 p-5">
                    <h2 class="mb-1 text-sm font-semibold text-amber-900">{{ __('Allergy Details') }}</h2>
                    <p class="mb-4 text-xs text-amber-700/70">{{ __('Optional — record an allergy for this patient at booking time. Leave blank to skip.') }}</p>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-amber-900/70">{{ __('Allergen') }}</label>
                            <select name="allergy_Drug_No" class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:outline-none">
                                <option value="">{{ __('— none —') }}</option>
                                @foreach ($allergyDrugs as $drug)
                                    <option value="{{ $drug->Drug_No }}" {{ old('allergy_Drug_No') == $drug->Drug_No ? 'selected' : '' }}>
                                        {{ $drug->Name }} ({{ $drug->Drug_No }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-amber-700/60">{{ __('The allergen name follows the selected drug.') }}</p>
                            @error('allergy_Drug_No')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-amber-900/70">{{ __('Reaction') }} <span class="text-red-500">*</span></label>
                            <textarea name="allergy_Reaction" rows="3" maxlength="150"
                                      placeholder="{{ __('e.g. Rash, swelling, difficulty breathing...') }}"
                                      class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:outline-none">{{ old('allergy_Reaction') }}</textarea>
                            @error('allergy_Reaction')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-amber-900/70">{{ __('Severity') }} <span class="text-red-500">*</span></label>
                            <select name="allergy_Severity" class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:outline-none">
                                <option value="">{{ __('Select') }}</option>
                                @foreach ($severities as $severity)
                                    <option value="{{ $severity }}" {{ old('allergy_Severity') === $severity ? 'selected' : '' }}>{{ __($severity) }}</option>
                                @endforeach
                            </select>
                            @error('allergy_Severity')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-amber-900/70">{{ __('Diagnosed Date') }} <span class="text-red-500">*</span></label>
                            <input type="date" name="allergy_DiagDate" value="{{ old('allergy_DiagDate') }}"
                                   class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:outline-none">
                            @error('allergy_DiagDate')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-amber-900/70">{{ __('Recorded By') }}</label>
                            <select name="allergy_Rec_Stf_No" class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm focus:border-amber-500 focus:outline-none">
                                <option value="">{{ __('— select —') }}</option>
                                @foreach ($allergyStaff as $member)
                                    <option value="{{ $member->Stf_No }}" {{ old('allergy_Rec_Stf_No') == $member->Stf_No ? 'selected' : '' }}>
                                        {{ $member->full_name }} ({{ $member->Stf_No }})
                                    </option>
                                @endforeach
                            </select>
                            @error('allergy_Rec_Stf_No')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
                @if (! $appointment->exists && request('room') && request('date'))
                    <a href="{{ route('rooms.show', ['room' => request('room'), 'date' => request('date')]) }}"
                       class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-200">
                        {{ __('Cancel') }}
                    </a>
                @else
                    <a href="{{ route('appointments.index') }}"
                       class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-200">
                        {{ __('Cancel') }}
                    </a>
                @endif
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    {{ $appointment->exists ? __('Save changes') : __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
