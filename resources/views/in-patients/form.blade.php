@extends('layouts.app')

@section('title', $inPatient->exists ? __('Edit Admission') : __('New Admission'))

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="rounded-2xl bg-white p-8 shadow-sm">
        <h1 class="mb-6 text-2xl font-bold text-slate-900">
            {{ $inPatient->exists ? __('Edit Admission') : __('New Admission') }}
        </h1>

        <form method="POST" action="{{ $inPatient->exists ? route('in-patients.update', $inPatient) : route('in-patients.store') }}">
            @csrf
            @if ($inPatient->exists)
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
                {{-- Main admission details --}}
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Admission No.') }}</label>
                        <input type="text" value="{{ $inPatient->exists ? $inPatient->In_Pt_No : $nextInPtNo }}" disabled
                               placeholder="{{ __('Auto-generated') }}"
                               class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-sm text-slate-500 cursor-not-allowed">
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $inPatient->exists ? __('Cannot be changed after creation') : __('Generated automatically when saved') }}
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Patient') }} <span class="text-red-500">*</span>
                        </label>
                        <x-patient-select name="Pt_No" :patients="$patients" :selected="old('Pt_No', $inPatient->Pt_No)" />
                        @error('Pt_No')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Ward') }}
                        </label>
                        <select name="ward" id="ward-select" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">{{ __('Select Ward') }}</option>
                            @foreach ($wards as $ward)
                                <option value="{{ $ward->Wd_No }}" {{ old('ward', $inPatient->bed->Wd_No ?? '') == $ward->Wd_No ? 'selected' : '' }}>
                                    {{ $ward->Wd_Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Bed') }}
                        </label>
                        <select name="Bed_No" id="bed-select" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">{{ __('Select Ward First') }}</option>
                        </select>
                        @error('Bed_No')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Wait List Date') }}</label>
                        <input type="date" name="DateWaitList" value="{{ old('DateWaitList', $inPatient->DateWaitList?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        @error('DateWaitList')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Expected Stay (Days)') }}</label>
                        <input type="number" name="ExpStayDays" value="{{ old('ExpStayDays', $inPatient->ExpStayDays) }}" min="1"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        @error('ExpStayDays')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Admission Date') }}</label>
                        <input type="date" name="DatePlaced" value="{{ old('DatePlaced', $inPatient->DatePlaced?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        @error('DatePlaced')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Expected Leave Date') }}</label>
                        <input type="date" name="DateLeave" value="{{ old('DateLeave', $inPatient->DateLeave?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        @error('DateLeave')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('Actual Leave Date') }}</label>
                        <input type="date" name="ActDateLeft" value="{{ old('ActDateLeft', $inPatient->ActDateLeft?->format('Y-m-d')) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        @error('ActDateLeft')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('in-patients.index') }}"
                   class="rounded-lg bg-slate-100 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-200">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    {{ $inPatient->exists ? __('Save changes') : __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Dynamic bed loading based on ward selection
    const wardSelect = document.getElementById('ward-select');
    const bedSelect = document.getElementById('bed-select');
    const currentBedNo = '{{ old('Bed_No', $inPatient->Bed_No ?? '') }}';
    const currentWardNo = '{{ old('ward', $inPatient->bed->Wd_No ?? '') }}';

    // Pre-load beds data
    const bedsData = @json($beds->groupBy('Wd_No')->map(function($beds) {
        return $beds->map(function($bed) {
            return ['Bed_No' => $bed->Bed_No, 'BedStatus' => $bed->BedStatus];
        });
    }));

    function loadBeds(wardNo) {
        bedSelect.innerHTML = '<option value="">{{ __('Select Bed') }}</option>';
        if (bedsData[wardNo]) {
            bedsData[wardNo].forEach(bed => {
                if (bed.BedStatus === 'Available' || bed.Bed_No === currentBedNo) {
                    const option = document.createElement('option');
                    option.value = bed.Bed_No;
                    option.textContent = bed.Bed_No + (bed.BedStatus !== 'Available' ? ' (Occupied)' : '');
                    if (bed.Bed_No === currentBedNo) option.selected = true;
                    bedSelect.appendChild(option);
                }
            });
        }
    }

    wardSelect.addEventListener('change', function() {
        loadBeds(this.value);
    });

    // Initialize on page load
    if (currentWardNo) {
        wardSelect.value = currentWardNo;
        loadBeds(currentWardNo);
    }
</script>
@endsection