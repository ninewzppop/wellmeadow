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
                        @if ($patient->exists)
                            <input type="text" value="{{ $patient->Pt_No }}" disabled
                                   class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500 cursor-not-allowed">
                            <p class="mt-1 text-xs text-slate-400">{{ __('Cannot be changed after creation') }}</p>
                        @else
                            <input type="text" value="" disabled
                                   placeholder="{{ __('Auto-generated after save') }}"
                                   class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500 cursor-not-allowed">
                            <p class="mt-1 text-xs text-slate-400">{{ __('Will be generated automatically when saved') }}</p>
                        @endif
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
                        @php
                            $selectedDoctor = $doctors->firstWhere('Clinic_No', old('Clinic_No', $patient->Clinic_No));
                            $doctorOptions = $doctors->map(fn ($d) => ['id' => $d->Clinic_No, 'label' => $d->full_name.' ('.$d->Clinic_No.')'])->values();
                        @endphp
                        <div data-doctor-select class="relative">
                            <input type="hidden" name="Clinic_No" value="{{ old('Clinic_No', $patient->Clinic_No) }}" data-doctor-value>
                            <input type="text" autocomplete="off" data-doctor-search
                                   value="{{ $selectedDoctor ? $selectedDoctor->full_name.' ('.$selectedDoctor->Clinic_No.')' : '' }}"
                                   placeholder="{{ __('Type to search doctor...') }}"
                                   data-doctor-options="{{ json_encode($doctorOptions) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                            <div data-doctor-list class="absolute z-20 mt-1 hidden max-h-60 w-full overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"></div>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">{{ __('Search and select — no need to leave this page.') }}</p>
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
@push('scripts')
<script>
    document.querySelectorAll('[data-doctor-select]').forEach(function (wrap) {
        var input = wrap.querySelector('[data-doctor-search]');
        var hidden = wrap.querySelector('[data-doctor-value]');
        var list = wrap.querySelector('[data-doctor-list]');
        var doctors = JSON.parse(input.dataset.doctorOptions || '[]');
        function render(q) {
            q = (q || '').trim().toLowerCase();
            var matches = doctors.filter(function (d) { return d.label.toLowerCase().indexOf(q) !== -1; }).slice(0, 50);
            list.innerHTML = '';
            if (!matches.length) {
                var empty = document.createElement('div');
                empty.className = 'px-3 py-2 text-sm text-slate-400';
                empty.textContent = @json(__('No matching doctor.'));
                list.appendChild(empty);
            }
            matches.forEach(function (d) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100';
                btn.textContent = d.label;
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    hidden.value = d.id;
                    input.value = d.label;
                    list.classList.add('hidden');
                });
                list.appendChild(btn);
            });
            list.classList.remove('hidden');
        }
        input.addEventListener('focus', function () { render(input.value); });
        input.addEventListener('input', function () { hidden.value = ''; render(input.value); });
        input.addEventListener('blur', function () { setTimeout(function () { list.classList.add('hidden'); }, 150); });
        // clear if typed text does not match hidden value
        input.addEventListener('change', function () {
            var exact = doctors.find(function (d) { return d.label === input.value; });
            if (!exact && input.value.trim() === '') hidden.value = '';
        });
    });
</script>
@endpush
@endsection