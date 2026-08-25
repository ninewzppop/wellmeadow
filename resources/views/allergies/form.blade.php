@extends('layouts.app')

@section('title', $allergy->exists ? __('Edit Allergy Record') : __('New Allergy Record'))

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="rounded-2xl bg-white p-8 shadow-sm">
        <h1 class="mb-6 text-2xl font-bold text-slate-900">
            {{ $allergy->exists ? __('Edit Allergy Record') : __('New Allergy Record') }}
        </h1>

        <form method="POST" action="{{ $allergy->exists ? route('allergies.update', $allergy) : route('allergies.store') }}">
            @csrf
            @if ($allergy->exists)
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

            {{-- Record information --}}
            <section>
                <h2 class="mb-5 border-b border-slate-100 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    {{ __('Record Information') }}
                </h2>
                <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">
                            {{ __('Allergy No.') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="Allergy_No" value="{{ old('Allergy_No', $allergy->Allergy_No) }}" maxlength="10"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none"
                               required @if($allergy->exists) readonly @endif>
                        @if($allergy->exists)
                            <p class="mt-1.5 text-xs text-slate-400">{{ __('Cannot be changed after creation') }}</p>
                        @endif
                        @error('Allergy_No')
                            <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">
                            {{ __('Diagnosed Date') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="DiagDate" value="{{ old('DiagDate', $allergy->DiagDate?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none" required>
                        @error('DiagDate')
                            <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Patient') }}</label>
                        <select name="Pt_No" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">{{ __('— select —') }}</option>
                            @foreach ($patients as $patient)
                                <option value="{{ $patient->Pt_No }}" {{ old('Pt_No', $allergy->Pt_No) == $patient->Pt_No ? 'selected' : '' }}>
                                    {{ $patient->full_name }} ({{ $patient->Pt_No }})
                                </option>
                            @endforeach
                        </select>
                        @error('Pt_No')
                            <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Recorded By') }}</label>
                        <select name="Rec_Stf_No" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">{{ __('— select —') }}</option>
                            @foreach ($staff as $member)
                                <option value="{{ $member->Stf_No }}" {{ old('Rec_Stf_No', $allergy->Rec_Stf_No) == $member->Stf_No ? 'selected' : '' }}>
                                    {{ $member->full_name }} ({{ $member->Stf_No }})
                                </option>
                            @endforeach
                        </select>
                        @error('Rec_Stf_No')
                            <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            {{-- Allergy details --}}
            <section class="mt-10">
                <h2 class="mb-5 border-b border-slate-100 pb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    {{ __('Allergy Details') }}
                </h2>
                <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Allergen') }}</label>
                        <input type="text" name="Allergy_Name" value="{{ old('Allergy_Name', $allergy->Allergy_Name) }}" maxlength="100"
                               placeholder="{{ __('e.g. Penicillin') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                        <p class="mt-1.5 text-xs text-slate-400">{{ __('Or link a specific drug from stock instead →') }}</p>
                        @error('Allergy_Name')
                            <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Linked Drug') }}</label>
                        <select name="Drug_No" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">{{ __('— none —') }}</option>
                            @foreach ($drugs as $drug)
                                <option value="{{ $drug->Drug_No }}" {{ old('Drug_No', $allergy->Drug_No) == $drug->Drug_No ? 'selected' : '' }}>
                                    {{ $drug->Name }} ({{ $drug->Drug_No }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-slate-400">{{ __('From the Pharmaceutical supplies list') }}</p>
                        @error('Drug_No')
                            <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">
                            {{ __('Reaction') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea name="Reaction" rows="3" maxlength="150"
                                  placeholder="{{ __('e.g. Rash, swelling, difficulty breathing...') }}"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none"
                                  required>{{ old('Reaction', $allergy->Reaction) }}</textarea>
                        @error('Reaction')
                            <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">
                            {{ __('Severity') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="Severity" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none" required>
                            <option value="">{{ __('Select') }}</option>
                            @foreach ($severities as $severity)
                                <option value="{{ $severity }}" {{ old('Severity', $allergy->Severity) === $severity ? 'selected' : '' }}>
                                    {{ __($severity) }}
                                </option>
                            @endforeach
                        </select>
                        @error('Severity')
                            <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="mt-10 flex justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('allergies.index') }}"
                   class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-200">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    {{ $allergy->exists ? __('Save changes') : __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection