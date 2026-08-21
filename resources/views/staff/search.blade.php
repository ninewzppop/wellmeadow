@extends('layouts.app')

@section('title', __('Search staff'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#112D6E]">{{ __('Search staff') }}</h1>
        <p class="mt-1 text-sm text-slate-500">
            {{ __('Find staff by qualification or previous work experience. Leave a field blank to ignore it.') }}
        </p>
    </div>

    <form method="GET" action="{{ route('staff.search') }}"
          class="mb-8 rounded-lg bg-white p-6 shadow">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="qualification" class="block text-sm font-medium text-slate-700">{{ __('Qualification') }}</label>
                <input type="text" id="qualification" name="qualification" value="{{ request('qualification') }}"
                       placeholder="{{ __('e.g. RGN, BSc Nursing') }}"
                       class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            </div>
            <div>
                <label for="institution" class="block text-sm font-medium text-slate-700">{{ __('Qualifying institution') }}</label>
                <input type="text" id="institution" name="institution" value="{{ request('institution') }}"
                       placeholder="{{ __('e.g. City University') }}"
                       class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            </div>
            <div>
                <label for="organization" class="block text-sm font-medium text-slate-700">{{ __('Previous employer') }}</label>
                <input type="text" id="organization" name="organization" value="{{ request('organization') }}"
                       placeholder="{{ __('e.g. St Mary\'s Hospital') }}"
                       class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            </div>
            <div>
                <label for="experience_position" class="block text-sm font-medium text-slate-700">{{ __('Previous role') }}</label>
                <input type="text" id="experience_position" name="experience_position" value="{{ request('experience_position') }}"
                       placeholder="{{ __('e.g. Ward Sister') }}"
                       class="mt-1 w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
            </div>
        </div>
        <div class="mt-4 flex gap-3">
            <button type="submit" class="rounded bg-sky-600 px-6 py-2 text-sm font-medium text-white hover:bg-sky-700">
                {{ __('Search') }}
            </button>
            <a href="{{ route('staff.search') }}" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-900">{{ __('Clear') }}</a>
        </div>
    </form>

    @if (isset($staff))
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">{{ __('Name') }}</th>
                        <th class="px-4 py-3">{{ __('Position(s)') }}</th>
                        <th class="px-4 py-3">{{ __('Assigned ward') }}</th>
                        <th class="px-4 py-3">{{ __('Qualifications') }}</th>
                        <th class="px-4 py-3">{{ __('Work experience') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($staff as $member)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-900">
                                {{ $member->full_name }}
                                <span class="block font-mono text-xs text-slate-400">{{ $member->Stf_No }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @forelse ($member->positions as $p)
                                    <span class="mb-1 inline-block rounded bg-indigo-50 px-2 py-0.5 text-xs text-indigo-700">
                                        {{ $p->pos->Pos_Name ?? $p->Pos_No }}
                                    </span>
                                @empty
                                    <span class="text-slate-400">&mdash;</span>
                                @endforelse
                            </td>
                            <td class="px-4 py-3">
                                @if ($member->assignedWard)
                                    <span class="rounded bg-cyan-50 px-2 py-0.5 text-xs text-cyan-700">
                                        {{ $member->assignedWard->Wd_Name }}
                                    </span>
                                @else
                                    <span class="text-slate-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @forelse ($member->qualifications as $q)
                                    <span class="mb-1 inline-block rounded bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                                        {{ $q->Type }} <span class="text-emerald-500">({{ $q->Institution }})</span>
                                    </span>
                                @empty
                                    <span class="text-slate-400">&mdash;</span>
                                @endforelse
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">
                                @forelse ($member->workExperiences as $e)
                                    {{ $e->Position }} @ {{ $e->Organization }}<br>
                                @empty
                                    <span class="text-slate-400">&mdash;</span>
                                @endforelse
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('staff.edit', $member) }}" class="text-sky-600 hover:text-sky-800">{{ __('Edit') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                                {{ __('No staff match the given criteria.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="mt-3 text-xs text-slate-500">
            {{ __(':count result(s) found.', ['count' => $staff->count()]) }}
        </p>
    @endif
@endsection