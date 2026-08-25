@extends('layouts.app')

@section('title', $appointment->exists ? __('Edit Appointment') : __('New Appointment'))

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="rounded-2xl bg-white p-8 shadow-sm">
        <h1 class="mb-1 text-2xl font-bold text-slate-900">
            {{ $appointment->exists ? __('Edit Appointment') : __('New Appointment') }}
        </h1>
        @if (! $appointment->exists)
            <p class="mb-6 text-sm text-slate-500">{{ __('The appointment number is generated automatically — just pick and save.') }}</p>
        @else
            <p class="mb-6 text-sm text-slate-500">{{ __('Appointment No.') }}: <span class="font-semibold text-slate-700">{{ $appointment->Appt_No }}</span></p>
        @endif

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

            <div class="grid grid-cols-1 gap-8">
                {{-- Main appointment details --}}
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            {{ __('Patient') }} <span class="text-red-500">*</span>
                        </label>
                        @php
                            $selectedPtNo = old('Pt_No', $appointment->Pt_No);
                            $selectedPatient = $selectedPtNo ? $patients->firstWhere('Pt_No', $selectedPtNo) : null;
                        @endphp
                        <input type="hidden" name="Pt_No" value="{{ $selectedPtNo }}">
                        <div class="relative">
                            <input type="text" id="patient-search" autocomplete="off"
                                   value="{{ $selectedPatient?->full_name ? $selectedPatient->full_name.' ('.$selectedPatient->Pt_No.')' : '' }}"
                                   placeholder="{{ __('Type patient name or ID...') }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                            <div id="patient-list"
                                 class="absolute z-20 mt-1 hidden max-h-60 w-full overflow-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"></div>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">{{ __('Type to search by name or patient ID.') }}</p>
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

@push('scripts')
    <script>
        (function () {
            var patients = @json($patients->map(fn ($p) => ['id' => $p->Pt_No, 'label' => $p->full_name.' ('.$p->Pt_No.')']));
            var input = document.getElementById('patient-search');
            var list = document.getElementById('patient-list');
            var hidden = document.querySelector('input[name="Pt_No"]');
            if (!input || !list || !hidden) {
                return;
            }

            function render(query) {
                query = query.trim().toLowerCase();
                var matches = patients.filter(function (p) {
                    return p.label.toLowerCase().indexOf(query) !== -1;
                }).slice(0, 50);

                list.innerHTML = '';

                if (!matches.length) {
                    var empty = document.createElement('div');
                    empty.className = 'px-3 py-2 text-sm text-slate-400';
                    empty.textContent = @json(__('No matching patient.'));
                    list.appendChild(empty);
                }

                matches.forEach(function (p) {
                    var option = document.createElement('button');
                    option.type = 'button';
                    option.className = 'block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100';
                    option.textContent = p.label;
                    option.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        hidden.value = p.id;
                        input.value = p.label;
                        list.classList.add('hidden');
                    });
                    list.appendChild(option);
                });

                list.classList.remove('hidden');
            }

            input.addEventListener('focus', function () {
                render(input.value);
            });
            input.addEventListener('input', function () {
                hidden.value = '';
                render(input.value);
            });
            input.addEventListener('blur', function () {
                setTimeout(function () {
                    list.classList.add('hidden');
                }, 120);
            });
        })();
    </script>
@endpush
