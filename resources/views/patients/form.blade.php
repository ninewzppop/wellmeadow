@extends('layouts.app')

@section('title', $patient->exists ? __('Edit Patient') : __('Add Patient'))

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="rounded-2xl bg-white p-8 shadow-sm">
        <h1 class="mb-6 text-2xl font-bold text-slate-900">
            {{ $patient->exists ? __('Edit Patient') : __('Register Patient') }}
        </h1>

        <form method="POST" action="{{ $patient->exists ? route('patients.update', $patient) : route('patients.store') }}">
            @csrf
            @if ($patient->exists)
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

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                {{-- Main patient details --}}
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2 lg:col-span-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Patient No.') }}</label>
                        <input type="text" value="{{ $patient->exists ? $patient->Pt_No : $nextPtNo }}" disabled
                               placeholder="{{ __('Auto-generated') }}"
                               class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500 cursor-not-allowed">
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $patient->exists ? __('Cannot be changed after creation') : __('Generated automatically when saved') }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Date Registered') }}</label>
                        <input type="date" name="DateReg" value="{{ old('DateReg', $patient->DateReg?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        @error('DateReg')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('First Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="FirstName" value="{{ old('FirstName', $patient->FirstName) }}" maxlength="50"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" required>
                        @error('FirstName')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Last Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="LastName" value="{{ old('LastName', $patient->LastName) }}" maxlength="50"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" required>
                        @error('LastName')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Date of Birth') }}</label>
                        <input type="date" name="DOB" value="{{ old('DOB', $patient->DOB?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        @error('DOB')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Sex') }}</label>
                        <select name="Sex" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">{{ __('Select') }}</option>
                            <option value="M" {{ old('Sex', $patient->Sex) === 'M' ? 'selected' : '' }}>{{ __('Male') }}</option>
                            <option value="F" {{ old('Sex', $patient->Sex) === 'F' ? 'selected' : '' }}>{{ __('Female') }}</option>
                        </select>
                        @error('Sex')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Address') }}</label>
                        <textarea name="Address" rows="2"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">{{ old('Address', $patient->Address) }}</textarea>
                        @error('Address')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Telephone') }}</label>
                        <input type="text" name="TelNo" value="{{ old('TelNo', $patient->TelNo) }}" maxlength="15"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        @error('TelNo')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Marital Status') }}</label>
                        <input type="text" name="MaritalStat" value="{{ old('MaritalStat', $patient->MaritalStat) }}" maxlength="15"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        @error('MaritalStat')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Local Doctor') }}</label>
                        <select name="Clinic_No" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">{{ __('Select') }}</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->Clinic_No }}" {{ old('Clinic_No', $patient->Clinic_No) == $doctor->Clinic_No ? 'selected' : '' }}>
                                    {{ $doctor->full_name }} ({{ $doctor->Clinic_No }})
                                </option>
                            @endforeach
                        </select>
                        @error('Clinic_No')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Next of kin (highlighted panel) --}}
                @php $kin = $patient->nextOfKin ?? null; @endphp
                <div class="rounded-2xl bg-blue-50 p-5">
                    <h2 class="mb-4 text-sm font-semibold text-blue-900">{{ __('Next of Kin') }}</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-blue-900/70">{{ __('Full Name') }}</label>
                            <input type="text" name="kin_full_name" value="{{ old('kin_full_name', $kin->FullName ?? '') }}"
                                   class="w-full rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-blue-900/70">{{ __('Relationship') }}</label>
                            <input type="text" name="kin_relationship" value="{{ old('kin_relationship', $kin->Relationship ?? '') }}"
                                   class="w-full rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        </div>
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="mb-1 block text-xs font-medium text-blue-900/70">{{ __('Address') }}</label>
                            <input type="text" name="kin_address" value="{{ old('kin_address', $kin->Address ?? '') }}"
                                   class="w-full rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        </div>
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="mb-1 block text-xs font-medium text-blue-900/70">{{ __('Telephone') }}</label>
                            <input type="text" name="kin_tel_no" value="{{ old('kin_tel_no', $kin->TelNo ?? '') }}"
                                   class="w-full rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('patients.index') }}"
                   class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-200">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    {{ $patient->exists ? __('Save changes') : __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection