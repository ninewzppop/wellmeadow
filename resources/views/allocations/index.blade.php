@extends('layouts.app')

@section('title', __('Allocations'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ __('Ward allocations (rota)') }}</h1>
        <p class="mt-1 text-sm text-slate-500">
            {{ __('Assign a staff member to a ward for a week starting on a given date (shift-based roster).') }}
            {{ __('Used by the') }} <a href="{{ route('wards.report') }}" class="text-sky-600 hover:underline">{{ __('ward report') }}</a>.
        </p>
    </div>

    <div class="mb-8 rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('New allocation') }}</h2>
        <form method="POST" action="{{ route('allocations.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            @csrf
            <div>
                <label for="Stf_No" class="block text-sm font-medium text-slate-700">{{ __('Staff member *') }}</label>
                <select id="Stf_No" name="Stf_No" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    <option value="">{{ __('— select —') }}</option>
                    @foreach ($staff as $member)
                        <option value="{{ $member->Stf_No }}" @selected(old('Stf_No') === $member->Stf_No)>
                            {{ $member->full_name }} ({{ $member->Stf_No }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="Wd_No" class="block text-sm font-medium text-slate-700">{{ __('Ward *') }}</label>
                <select id="Wd_No" name="Wd_No" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    <option value="">{{ __('— select —') }}</option>
                    @foreach ($wards as $ward)
                        <option value="{{ $ward->Wd_No }}" @selected(old('Wd_No') === $ward->Wd_No)>
                            {{ $ward->Wd_Name }} ({{ $ward->Wd_No }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="WkBegin" class="block text-sm font-medium text-slate-700">{{ __('Week beginning *') }}</label>
                <input type="date" id="WkBegin" name="WkBegin" value="{{ old('WkBegin', today()->startOfWeek()->toDateString()) }}"
                       class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            </div>
            <div>
                <label for="Shift" class="block text-sm font-medium text-slate-700">{{ __('Shift *') }}</label>
                <select id="Shift" name="Shift" class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    <option value="Morning" @selected(old('Shift') === 'Morning')>{{ __('Morning') }}</option>
                    <option value="Evening" @selected(old('Shift') === 'Evening')>{{ __('Evening') }}</option>
                    <option value="Night" @selected(old('Shift') === 'Night')>{{ __('Night') }}</option>
                </select>
            </div>
            <div class="md:col-span-4">
                <button type="submit" class="rounded bg-sky-600 px-6 py-2 text-sm font-medium text-white hover:bg-sky-700">
                    {{ __('Add allocation') }}
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">{{ __('Week beginning') }}</th>
                    <th class="px-4 py-3">{{ __('Shift') }}</th>
                    <th class="px-4 py-3">{{ __('Staff') }}</th>
                    <th class="px-4 py-3">{{ __('Ward') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($allocations as $alloc)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $alloc->WkBegin?->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $alloc->Shift }}</td>
                        <td class="px-4 py-3">
                            {{ $alloc->stf->full_name ?? __('Deleted staff') }}
                            <span class="block font-mono text-xs text-slate-400">{{ $alloc->Stf_No }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $alloc->wd->Wd_Name ?? __('Deleted ward') }}</td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('allocations.destroy', $alloc) }}" method="POST"
                                  onsubmit="return confirm('{{ addslashes(__('Remove this allocation?')) }}');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Remove') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400">{{ __('No allocations yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection