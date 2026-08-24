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

            <div class="grid grid-cols-1 gap-8">
                {{-- Main appointment details --}}
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Appointment No.') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="Appt_No" value="{{ old('Appt_No', $appointment->Appt_No) }}" maxlength="10"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                               required @if($appointment->exists) readonly @endif>
                        @if($appointment->exists)
                            <p class="mt-1 text-xs text-slate-400">{{ __('Cannot be changed after creation') }}</p>
                        @endif
                        @error('Appt_No')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Status') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" required>
                            <option value="">{{ __('Select') }}</option>
                            <option value="scheduled" {{ old('status', $appointment->status) === 'scheduled' ? 'selected' : '' }}>{{ __('Scheduled') }}</option>
                            <option value="completed" {{ old('status', $appointment->status) === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                            <option value="cancelled" {{ old('status', $appointment->status) === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                            <option value="no-show" {{ old('status', $appointment->status) === 'no-show' ? 'selected' : '' }}>{{ __('No Show') }}</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Patient') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="Pt_No" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" required>
                            <option value="">{{ __('Select Patient') }}</option>
                            @foreach ($patients as $patient)
                                <option value="{{ $patient->Pt_No }}" {{ old('Pt_No', $appointment->Pt_No) == $patient->Pt_No ? 'selected' : '' }}>
                                    {{ $patient->full_name }} ({{ $patient->Pt_No }})
                                </option>
                            @endforeach
                        </select>
                        @error('Pt_No')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
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

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Room') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="Room_No" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" required>
                            <option value="">{{ __('Select Room') }}</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->Room_No }}" {{ old('Room_No', $appointment->Room_No) == $room->Room_No ? 'selected' : '' }}>
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
                            {{ __('Appointment Date') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="ApptDate" value="{{ old('ApptDate', $appointment->ApptDate?->format('Y-m-d')) }}"
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
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('appointments.index') }}"
                   class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-200">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    {{ $appointment->exists ? __('Save changes') : __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection