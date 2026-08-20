@extends('layouts.app')

@section('title', 'Staff')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Staff</h1>
        <div class="flex gap-3">
            <a href="{{ route('staff.search') }}"
               class="rounded bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">
                Search staff
            </a>
            <a href="{{ route('staff.create') }}"
               class="rounded bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700">
                + New staff member
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Position(s)</th>
                    <th class="px-4 py-3">Assigned ward</th>
                    <th class="px-4 py-3">Qualifications</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($staff as $member)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $member->Stf_No }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $member->full_name }}</td>
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
                                    {{ $q->Type }}
                                </span>
                            @empty
                                <span class="text-slate-400">&mdash;</span>
                            @endforelse
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">
                            {{ $member->TelNo }}<br>{{ $member->Address }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('staff.edit', $member) }}" class="text-sky-600 hover:text-sky-800">Edit</a>
                            <form action="{{ route('staff.destroy', $member) }}" method="POST"
                                  onsubmit="return confirm('Delete {{ $member->full_name }}?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-2 text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                            No staff records yet.
                            <a href="{{ route('staff.create') }}" class="text-sky-600 hover:underline">Add the first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $staff->links() }}
    </div>
@endsection