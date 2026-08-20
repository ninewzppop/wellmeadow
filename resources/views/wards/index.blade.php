@extends('layouts.app')

@section('title', 'Wards')

@section('content')
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Ward Management</h1>
            <p class="mt-1 text-sm text-slate-500">
                Manage hospital wards and bed location
            </p>
        </div>
    </div>

    {{-- Ward cards grid --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @forelse ($wards as $ward)
            @php
                $stats = $bedStats[$ward->Wd_No] ?? null;
                $available = $stats->available_beds ?? 0;
                $occupied = $stats->occupied_beds ?? 0;
                $total = $ward->TotalBeds;
            @endphp
            <a href="{{ route('wards.show', $ward) }}"
               class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $ward->Wd_Name }}</h2>
                        <p class="mt-0.5 font-mono text-xs text-slate-400">{{ $ward->Wd_No }}</p>
                    </div>
                    <span class="whitespace-nowrap rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                        {{ $occupied }}/{{ $total }} Beds
                    </span>
                </div>

                <div class="mt-4 space-y-2 text-sm text-slate-600">
                    <div class="flex items-center gap-2">
                        <span aria-hidden="true">&#128205;</span>
                        <span>{{ $ward->Location }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span aria-hidden="true">&#128222;</span>
                        <span>Extn {{ $ward->TelExtension }}</span>
                    </div>
                </div>
            </a>
        @empty
            <p class="col-span-full rounded-lg bg-white px-6 py-10 text-center text-slate-400 shadow">
                No wards defined yet.
            </p>
        @endforelse
    </div>
@endsection