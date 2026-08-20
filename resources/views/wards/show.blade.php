@extends('layouts.app')

@section('title', $ward->Wd_Name)

@section('content')
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('wards.index') }}"
               class="flex h-9 w-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100">
                &larr;
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    {{ $ward->Wd_No }} &ndash; {{ $ward->Wd_Name }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $ward->Location }} &middot; {{ $ward->TotalBeds }} beds &middot; ext. {{ $ward->TelExtension }}
                </p>
            </div>
        </div>

        <div class="flex gap-2">
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700">
                {{ $available }} available
            </span>
            <span class="rounded-full bg-rose-100 px-3 py-1 text-sm font-medium text-rose-700">
                {{ $occupied }} occupied
            </span>
        </div>
    </div>

    {{-- Bed Map card --}}
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-slate-700">Bed Map</h2>

        @if ($beds->isEmpty())
            <p class="py-10 text-center text-sm text-slate-400">No beds recorded for this ward.</p>
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                @foreach ($beds as $bed)
                    @php
                        $isAvailable = $bed->BedStatus === 'Available';
                    @endphp
                    <div class="rounded-xl border px-3 py-3 text-center
                        {{ $isAvailable
                            ? 'border-emerald-300 bg-emerald-50'
                            : 'border-red-300 bg-red-50' }}">
                        <p class="text-sm font-semibold
                            {{ $isAvailable ? 'text-emerald-800' : 'text-red-700' }}">
                            Bed {{ $bed->Bed_No }}
                        </p>
                        <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-semibold
                            {{ $isAvailable ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                            {{ $bed->BedStatus }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Ward Staff card (only renders if $staff is passed from controller) --}}
    @isset($staff)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-slate-700">Ward Staff</h2>

            @if ($staff->isEmpty())
                <p class="py-10 text-center text-sm text-slate-400">No staff assigned to this ward.</p>
            @else
                <div class="space-y-2">
                    @foreach ($staff as $member)
                        <div class="flex items-center gap-3 rounded-lg bg-slate-100 px-4 py-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-slate-500">
                                &#128100;
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $member->Name }}</p>
                                <p class="text-xs text-slate-500">{{ $member->Role }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endisset
@endsection