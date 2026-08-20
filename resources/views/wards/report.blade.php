@extends('layouts.app')

@section('title', __('Ward Report'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Staff allocated per ward') }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ __('Shift roster allocations (StfRota) and each staff member\'s primary ward.') }}
            </p>
        </div>

        <form method="GET" action="{{ route('wards.report') }}" class="flex items-end gap-2">
            <div>
                <label for="date" class="block text-sm font-medium text-slate-700">{{ __('Filter by week beginning') }}</label>
                <input type="date" id="date" name="date" value="{{ $weekBeginning ?? '' }}"
                       class="mt-1 rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            </div>
            <button type="submit" class="rounded bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">
                {{ __('Filter') }}
            </button>
            @if (!empty($weekBeginning))
                <a href="{{ route('wards.report') }}" class="px-3 py-2 text-sm text-slate-600 hover:text-slate-900">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <div class="space-y-6">
        @forelse ($wards as $ward)
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-slate-200 bg-slate-50 px-4 py-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ $ward->Wd_Name }}</h2>
                        <p class="text-xs text-slate-500">
                            {{ $ward->Wd_No }} &middot; {{ $ward->Location }} &middot; {{ $ward->TotalBeds }} {{ __('beds') }}
                            &middot; {{ __('ext.') }} {{ $ward->TelExtension }}
                        </p>
                    </div>
                    <span class="text-xs font-medium text-slate-500">
                        {{ __(':count roster allocation(s)', ['count' => $ward->rotas->count()]) }}
                        @if (!empty($weekBeginning))
                            {{ __('for week beginning :date', ['date' => \Carbon\Carbon::parse($weekBeginning)->format('d M Y')]) }}
                        @endif
                    </span>
                </div>

                @if ($ward->rotas->isNotEmpty())
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                                <th class="px-4 py-2">{{ __('Week beginning') }}</th>
                                <th class="px-4 py-2">{{ __('Shift') }}</th>
                                <th class="px-4 py-2">{{ __('Staff') }}</th>
                                <th class="px-4 py-2">{{ __('Position') }}</th>
                                <th class="px-4 py-2">{{ __('Contact') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($ward->rotas->sortByDesc('WkBegin') as $rota)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $rota->WkBegin?->format('d M Y') }}</td>
                                    <td class="px-4 py-2">
                                        <span class="rounded px-2 py-0.5 text-xs font-medium
                                            {{ $rota->Shift === 'Night' ? 'bg-slate-800 text-white' : ($rota->Shift === 'Evening' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800') }}">
                                            {{ $rota->Shift }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 font-medium text-slate-900">
                                        {{ $rota->stf?->full_name ?? __('Deleted staff') }}
                                    </td>
                                    <td class="px-4 py-2 text-xs text-slate-500">
                                        @forelse ($rota->stf?->positions ?? [] as $p)
                                            {{ $p->pos->Pos_Name ?? $p->Pos_No }}
                                        @empty
                                            &mdash;
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-2 text-xs text-slate-500">{{ $rota->stf?->TelNo }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="px-4 py-6 text-center text-sm text-slate-400">
                        {{ __('No staff allocated') }}
                        @if (!empty($weekBeginning)) {{ __('for week beginning :date', ['date' => \Carbon\Carbon::parse($weekBeginning)->format('d M Y')]) }}. @endif
                    </p>
                @endif
            </div>
        @empty
            <p class="rounded-lg bg-white px-6 py-10 text-center text-slate-400 shadow">
                {{ __('No wards defined yet.') }}
            </p>
        @endforelse
    </div>
@endsection