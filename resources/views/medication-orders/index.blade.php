@extends('layouts.app')

@section('title', __('Dispensing queue'))

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Dispensing queue') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Medication orders waiting to be dispensed and paid.') }}</p>
        </div>
        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
            {{ __('Pending') }}: {{ $orders->count() }}
        </span>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif
    @if ($errors->has('queue'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first('queue') }}</div>
    @endif

    @if ($orders->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
            <p class="text-sm font-medium text-slate-600">{{ __('No pending medication orders.') }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ __('Orders sent from the consultation room will appear here.') }}</p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-3">{{ __('Order time') }}</th>
                            <th class="px-5 py-3">{{ __('Patient') }}</th>
                            <th class="px-5 py-3">{{ __('Prescriber') }}</th>
                            <th class="px-5 py-3">{{ __('Items') }}</th>
                            <th class="px-5 py-3">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($orders as $order)
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3 text-slate-600">
                                    {{ $order->OrderedAt?->format('d/m/Y H:i') ?? '—' }}
                                    <span class="block text-xs text-slate-400">{{ $order->Order_No }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-semibold text-slate-900">{{ $order->patient?->full_name ?? '—' }}</span>
                                    <span class="block text-xs text-slate-400">{{ __('HN') }} {{ $order->Pt_No }}</span>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $order->prescriber?->full_name ?? $order->Stf_No ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                        {{ $order->items->count() }} {{ Str::plural(__('drug'), $order->items->count()) }}
                                    </span>
                                    <span class="mt-1 block max-w-[260px] truncate text-xs text-slate-400">
                                        {{ $order->items->map(fn ($i) => $i->drug?->Name ?? $i->Drug_No)->implode(', ') }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">{{ __('Pending') }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('medications.show', $order) }}"
                                       class="inline-flex rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
