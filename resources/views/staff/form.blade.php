@extends('layouts.app')

@section('title', $staff->exists ? __('Edit Staff') : __('New Staff'))

@section('content')
    @php
        $editing = $staff->exists;
        $qualifications = $editing ? $staff->qualifications : collect();
        $workExperiences = $editing ? $staff->workExperiences : collect();
        $positions = $positions ?? collect();
        $staffPositions = $editing ? $staff->positions : collect();
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $editing ? __('Edit staff member') : __('New staff member') }}</h1>
    </div>

    <form method="POST"
          action="{{ $editing ? route('staff.update', $staff) : route('staff.store') }}"
          class="space-y-8">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <section class="rounded-lg bg-white p-6 shadow">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Personal details') }}</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="Stf_No" class="block text-sm font-medium text-slate-700">{{ __('Staff No *') }}</label>
                    <input type="text" id="Stf_No" name="Stf_No" value="{{ old('Stf_No', $staff->Stf_No) }}"
                           {{ $editing ? 'readonly' : '' }}
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none {{ $editing ? 'bg-slate-100' : '' }}">
                </div>
                <div>
                    <label for="NIN" class="block text-sm font-medium text-slate-700">{{ __('National Insurance No') }}</label>
                    <input type="text" id="NIN" name="NIN" value="{{ old('NIN', $staff->NIN) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label for="FirstName" class="block text-sm font-medium text-slate-700">{{ __('First name *') }}</label>
                    <input type="text" id="FirstName" name="FirstName" value="{{ old('FirstName', $staff->FirstName) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label for="LastName" class="block text-sm font-medium text-slate-700">{{ __('Last name *') }}</label>
                    <input type="text" id="LastName" name="LastName" value="{{ old('LastName', $staff->LastName) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label for="DOB" class="block text-sm font-medium text-slate-700">{{ __('Date of birth') }}</label>
                    <input type="date" id="DOB" name="DOB" value="{{ old('DOB', optional($staff->DOB)->format('Y-m-d')) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label for="Sex" class="block text-sm font-medium text-slate-700">{{ __('Sex') }}</label>
                    <select id="Sex" name="Sex"
                            class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                        <option value="">&mdash;</option>
                        @foreach (['M', 'F'] as $sex)
                            <option value="{{ $sex }}" @selected(old('Sex', $staff->Sex) === $sex)>{{ $sex }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="Alloc_Wd_No" class="block text-sm font-medium text-slate-700">{{ __('Assigned ward') }}</label>
                    <select id="Alloc_Wd_No" name="Alloc_Wd_No"
                            class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                        <option value="">{{ __('— none —') }}</option>
                        @foreach ($wards as $ward)
                            <option value="{{ $ward->Wd_No }}" @selected(old('Alloc_Wd_No', $staff->Alloc_Wd_No) === $ward->Wd_No)>
                                {{ $ward->Wd_Name }} ({{ $ward->Wd_No }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="Address" class="block text-sm font-medium text-slate-700">{{ __('Address') }}</label>
                    <input type="text" id="Address" name="Address" value="{{ old('Address', $staff->Address) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
                <div>
                    <label for="TelNo" class="block text-sm font-medium text-slate-700">{{ __('Telephone') }}</label>
                    <input type="text" id="TelNo" name="TelNo" value="{{ old('TelNo', $staff->TelNo) }}"
                           class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                </div>
            </div>
        </section>

        <section class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Positions') }}</h2>
                <button type="button" onclick="addPosRow()"
                        class="rounded bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">
                    {{ __('+ Add position') }}
                </button>
            </div>
            <div id="positions" class="space-y-3">
                @foreach ($staffPositions as $sp)
                    <div class="grid grid-cols-1 gap-3 rounded border border-slate-200 p-3 md:grid-cols-5">
                        <input type="hidden" name="positions[{{ $loop->index }}][StfPos_No]" value="{{ $sp->StfPos_No }}">
                        <div>
                            <label class="block text-xs font-medium text-slate-500">{{ __('Position *') }}</label>
                            <select name="positions[{{ $loop->index }}][Pos_No]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                                @foreach ($positions as $p)
                                    <option value="{{ $p->Pos_No }}" @selected($sp->Pos_No === $p->Pos_No)>{{ $p->Pos_Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500">{{ __('Salary') }}</label>
                            <input type="number" step="0.01" name="positions[{{ $loop->index }}][CurrSalary]" value="{{ $sp->CurrSalary }}"
                                   class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500">{{ __('Hours / wk') }}</label>
                            <input type="number" step="0.01" name="positions[{{ $loop->index }}][HrsPerWk]" value="{{ $sp->HrsPerWk }}"
                                   class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500">{{ __('Contract type') }}</label>
                            <input type="text" name="positions[{{ $loop->index }}][ContractType]" value="{{ $sp->ContractType }}"
                                   class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-slate-500">{{ __('Payment type') }}</label>
                                <input type="text" name="positions[{{ $loop->index }}][PaymentType]" value="{{ $sp->PaymentType }}"
                                       class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            </div>
                            <button type="button" onclick="this.closest('.grid').remove()"
                                    class="rounded bg-red-50 px-2 py-1.5 text-xs text-red-600 hover:bg-red-100">&times;</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Qualifications') }}</h2>
                <button type="button" onclick="addQualRow()"
                        class="rounded bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">
                    {{ __('+ Add qualification') }}
                </button>
            </div>
            <div id="qualifications" class="space-y-3">
                @foreach ($qualifications as $q)
                    <div class="grid grid-cols-1 gap-3 rounded border border-slate-200 p-3 md:grid-cols-4">
                        <input type="hidden" name="qualifications[{{ $loop->index }}][Qual_No]" value="{{ $q->Qual_No }}">
                        <div>
                            <label class="block text-xs font-medium text-slate-500">{{ __('Type') }}</label>
                            <input type="text" name="qualifications[{{ $loop->index }}][Type]" value="{{ $q->Type }}"
                                   class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500">{{ __('Date') }}</label>
                            <input type="date" name="qualifications[{{ $loop->index }}][QualDate]" value="{{ optional($q->QualDate)->format('Y-m-d') }}"
                                   class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500">{{ __('Institution') }}</label>
                            <input type="text" name="qualifications[{{ $loop->index }}][Institution]" value="{{ $q->Institution }}"
                                   class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <div class="flex items-end justify-end">
                            <button type="button" onclick="this.closest('.grid').remove()"
                                    class="rounded bg-red-50 px-2 py-1.5 text-xs text-red-600 hover:bg-red-100">&times;</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-lg bg-white p-6 shadow">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Work experience') }}</h2>
                <button type="button" onclick="addExpRow()"
                        class="rounded bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-200">
                    {{ __('+ Add experience') }}
                </button>
            </div>
            <div id="experiences" class="space-y-3">
                @foreach ($workExperiences as $e)
                    <div class="grid grid-cols-1 gap-3 rounded border border-slate-200 p-3 md:grid-cols-5">
                        <input type="hidden" name="work_experiences[{{ $loop->index }}][WorkExp_No]" value="{{ $e->WorkExp_No }}">
                        <div>
                            <label class="block text-xs font-medium text-slate-500">{{ __('Organization') }}</label>
                            <input type="text" name="work_experiences[{{ $loop->index }}][Organization]" value="{{ $e->Organization }}"
                                   class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500">{{ __('Position') }}</label>
                            <input type="text" name="work_experiences[{{ $loop->index }}][Position]" value="{{ $e->Position }}"
                                   class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500">{{ __('Start date') }}</label>
                            <input type="date" name="work_experiences[{{ $loop->index }}][StartDate]" value="{{ optional($e->StartDate)->format('Y-m-d') }}"
                                   class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500">{{ __('Finish date') }}</label>
                            <input type="date" name="work_experiences[{{ $loop->index }}][FinishDate]" value="{{ optional($e->FinishDate)->format('Y-m-d') }}"
                                   class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <div class="flex items-end justify-end">
                            <button type="button" onclick="this.closest('.grid').remove()"
                                    class="rounded bg-red-50 px-2 py-1.5 text-xs text-red-600 hover:bg-red-100">&times;</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="rounded bg-sky-600 px-6 py-2 text-sm font-medium text-white hover:bg-sky-700">
                {{ $editing ? __('Save changes') : __('Create staff member') }}
            </button>
            <a href="{{ route('staff.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('Cancel') }}</a>
        </div>
    </form>

    <template id="pos-row-template">
        <div class="grid grid-cols-1 gap-3 rounded border border-slate-200 p-3 md:grid-cols-5">
            <input type="hidden" name="positions[__IDX__][StfPos_No]" value="">
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Position *') }}</label>
                <select name="positions[__IDX__][Pos_No]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                    @foreach ($positions as $p)
                        <option value="{{ $p->Pos_No }}">{{ $p->Pos_Name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Salary') }}</label>
                <input type="number" step="0.01" name="positions[__IDX__][CurrSalary]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Hours / wk') }}</label>
                <input type="number" step="0.01" name="positions[__IDX__][HrsPerWk]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Contract type') }}</label>
                <input type="text" name="positions[__IDX__][ContractType]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-500">{{ __('Payment type') }}</label>
                    <input type="text" name="positions[__IDX__][PaymentType]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                </div>
                <button type="button" onclick="this.closest('.grid').remove()"
                        class="rounded bg-red-50 px-2 py-1.5 text-xs text-red-600 hover:bg-red-100">&times;</button>
            </div>
        </div>
    </template>

    <template id="qual-row-template">
        <div class="grid grid-cols-1 gap-3 rounded border border-slate-200 p-3 md:grid-cols-4">
            <input type="hidden" name="qualifications[__IDX__][Qual_No]" value="">
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Type') }}</label>
                <input type="text" name="qualifications[__IDX__][Type]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Date') }}</label>
                <input type="date" name="qualifications[__IDX__][QualDate]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Institution') }}</label>
                <input type="text" name="qualifications[__IDX__][Institution]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <div class="flex items-end justify-end">
                <button type="button" onclick="this.closest('.grid').remove()"
                        class="rounded bg-red-50 px-2 py-1.5 text-xs text-red-600 hover:bg-red-100">&times;</button>
            </div>
        </div>
    </template>

    <template id="exp-row-template">
        <div class="grid grid-cols-1 gap-3 rounded border border-slate-200 p-3 md:grid-cols-5">
            <input type="hidden" name="work_experiences[__IDX__][WorkExp_No]" value="">
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Organization') }}</label>
                <input type="text" name="work_experiences[__IDX__][Organization]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Position') }}</label>
                <input type="text" name="work_experiences[__IDX__][Position]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Start date') }}</label>
                <input type="date" name="work_experiences[__IDX__][StartDate]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">{{ __('Finish date') }}</label>
                <input type="date" name="work_experiences[__IDX__][FinishDate]" class="mt-1 w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
            </div>
            <div class="flex items-end justify-end">
                <button type="button" onclick="this.closest('.grid').remove()"
                        class="rounded bg-red-50 px-2 py-1.5 text-xs text-red-600 hover:bg-red-100">&times;</button>
            </div>
        </div>
    </template>

    <script>
        let rowCounter = 100;

        function addRow(templateId, containerId) {
            const template = document.getElementById(templateId);
            const row = template.content.cloneNode(true);
            row.querySelectorAll('[name*="__IDX__"]').forEach(el => {
                el.name = el.name.replace('__IDX__', rowCounter);
            });
            document.getElementById(containerId).appendChild(row);
            rowCounter++;
        }

        function addPosRow() { addRow('pos-row-template', 'positions'); }
        function addQualRow() { addRow('qual-row-template', 'qualifications'); }
        function addExpRow() { addRow('exp-row-template', 'experiences'); }
    </script>
@endsection