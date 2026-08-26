@extends('layouts.app')

@section('title', __('Movement history'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#112D6E]">
            {{ __('Stock movements') }} — {{ $item->Name }}
            <span class="ml-2 font-mono text-base font-normal text-slate-400">{{ $item->Drug_No }}</span>
        </h1>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">{{ __('Date') }}</th>
                    <th class="px-4 py-3">{{ __('Change') }}</th>
                    <th class="px-4 py-3">{{ __('Type') }}</th>
                    <th class="px-4 py-3">{{ __('Note') }}</th>
                    <th class="px-4 py-3">{{ __('By') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($movements as $movement)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-600">{{ $movement->MoveDate?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-bold {{ $movement->QtyChange > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $movement->QtyChange > 0 ? '+' : '' }}{{ $movement->QtyChange }}
                        </td>
                        <td class="px-4 py-3"><x-stock-status-badge :status="$movement->QtyChange > 0 ? 'normal' : 'out'" :label="$movement->directionLabel()" /></td>
                        <td class="px-4 py-3 text-slate-600">{{ $movement->Note ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $movement->user?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400">{{ __('No stock movements recorded yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex items-center gap-6">
        <a href="{{ route('pharmacy.index') }}" class="text-sm text-sky-600 hover:underline">&larr; {{ __('Back to pharmacy') }}</a>
        {{ $movements->links() }}
    </div>
@endsection
