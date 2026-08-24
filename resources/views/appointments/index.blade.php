@extends('layouts.app')

@section('title', __('Appointments'))

@section('content')
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Appointments') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Manage patient appointments and schedules') }}
            </p>
        </div>
        <a href="{{ route('appointments.create') }}"
           class="flex items-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
            <span aria-hidden="true">+</span> {{ __('New Appointment') }}
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="min-w-[200px] flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Search') }}</label>
            <input type="search" name="search" value="{{ request('search') }}"
                   placeholder="{{ __('Appt No., Patient, Doctor...') }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        </div>

        <div class="grid min-w-[280px] grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Date From') }}</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Date To') }}</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>
        </div>

        <div class="grid min-w-[280px] grid-cols-2 gap-2">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Status') }}</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst(str_replace('-', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Doctor') }}</label>
                <select name="consultant" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($consultants as $doctor)
                        <option value="{{ $doctor->Stf_No }}" @selected(request('consultant') == $doctor->Stf_No)>
                            {{ $doctor->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="min-w-[180px]">
            <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Room') }}</label>
            <select name="room" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                <option value="">{{ __('All') }}</option>
                @foreach ($rooms as $room)
                    <option value="{{ $room->Room_No }}" @selected(request('room') == $room->Room_No)>
                        {{ $room->RoomName }} ({{ $room->Room_No }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                {{ __('Filter') }}
            </button>
            <a href="{{ route('appointments.index') }}"
               class="rounded-lg px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                {{ __('Clear') }}
            </a>
        </div>
    </form>

    @if ($appointments->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-400 shadow-sm">
            {{ __('No appointments found.') }}
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-6 py-4">{{ __('Appt No.') }}</th>
                            <th class="px-6 py-4">{{ __('Date / Time') }}</th>
                            <th class="px-6 py-4">{{ __('Patient') }}</th>
                            <th class="px-6 py-4">{{ __('Doctor') }}</th>
                            <th class="px-6 py-4">{{ __('Room') }}</th>
                            <th class="px-6 py-4">{{ __('Status') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($appointments as $appt)
                            @php
                                $statusClass = match($appt->status) {
                                    'scheduled' => 'bg-sky-100 text-sky-700',
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    'no-show' => 'bg-amber-100 text-amber-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-slate-700">{{ $appt->Appt_No }}</td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $appt->ApptDate->format('d/m/Y') }}<br>
                                    <span class="text-xs">{{ $appt->ApptTime->format('H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-900">
                                    {{ $appt->patient->full_name ?? '—' }}<br>
                                    <span class="text-xs text-slate-400">{{ $appt->patient->Pt_No ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $appt->consultant->full_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $appt->room->RoomName ?? '—' }} ({{ $appt->room->Room_No ?? '—' }})</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst(str_replace('-', ' ', $appt->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('appointments.show', $appt) }}"
                                           title="{{ __('View') }}"
                                           class="text-slate-400 hover:text-slate-700">
                                            &#128065;
                                        </a>
                                        <a href="{{ route('appointments.edit', $appt) }}"
                                           title="{{ __('Edit') }}"
                                           class="text-slate-400 hover:text-slate-700">
                                            &#9998;
                                        </a>
                                        <form method="POST" action="{{ route('appointments.destroy', $appt) }}"
                                              onsubmit="return confirm('{{ __('Delete this appointment?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="{{ __('Delete') }}"
                                                    class="text-red-400 hover:text-red-600">
                                                &#128465;
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 flex justify-center">
            {{ $appointments->links() }}
        </div>
    @endif
@endsection