@extends('layouts.app')

@section('title', __('Rooms'))

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Consultation Rooms') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Pick a room to view and manage its patient queue.') }}
            </p>
        </div>

        {{-- Board-level queue date --}}
        <form method="GET" action="{{ route('rooms.index') }}"
              class="flex items-end gap-2 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">{{ __('Queue date') }}</label>
                <input type="date" name="date" value="{{ $date->toDateString() }}"
                       onchange="this.form.submit()"
                       class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none">
            </div>
            @if (! $date->isToday())
                <a href="{{ route('rooms.index') }}"
                   class="rounded-lg px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                    {{ __('Today') }}
                </a>
            @endif
        </form>
    </div>

    <p class="mb-4 -mt-2 text-xs font-medium uppercase tracking-wide text-slate-400">
        {{ __('Showing queues for :date', ['date' => $date->format('d/m/Y')]) }}
    </p>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($rooms as $room)
            @php
                $stats = $summary->get($room->Room_No, ['waiting' => 0, 'inConsultation' => 0, 'completed' => 0]);
            @endphp
            <a href="{{ route('rooms.show', ['room' => $room->Room_No, 'date' => $date->toDateString()]) }}"
               class="group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 group-hover:text-blue-600">
                            {{ $room->RoomName ?? __('Room') }} <span class="text-xs font-normal text-slate-400">{{ $room->Room_No }}</span>
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $room->Location ?? '—' }}</p>
                    </div>
                    @if ($stats['inConsultation'] > 0)
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-500 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-600"></span>
                            </span>
                            {{ __('In consultation') }}
                        </span>
                    @endif
                </div>

                <dl class="mt-4 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-xl bg-sky-50 px-2 py-2.5">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-sky-600">{{ __('Waiting') }}</dt>
                        <dd class="text-xl font-bold text-sky-700">{{ $stats['waiting'] }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-2 py-2.5">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">{{ __('Done') }}</dt>
                        <dd class="text-xl font-bold text-slate-700">{{ $stats['completed'] }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-2 py-2.5">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-500">{{ __('Total') }}</dt>
                        <dd class="text-xl font-bold text-slate-700">{{ $stats['waiting'] + $stats['inConsultation'] + $stats['completed'] }}</dd>
                    </div>
                </dl>

                <p class="mt-4 text-xs font-medium text-blue-600 group-hover:underline">{{ __('View queue →') }}</p>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-400 shadow-sm">
                {{ __('No rooms found.') }}
            </div>
        @endforelse
    </div>
@endsection
