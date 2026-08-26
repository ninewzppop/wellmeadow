@extends('layouts.app')

@section('title', __('Requisition Reports'))

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ __('Requisition Reports') }}</h1>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-slate-700">{{ __('Supplied per ward — by date range') }}</h2>
        <form method="GET" class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-4">
            <select name="ward" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">{{ __('All wards') }}</option>
                @foreach($wards as $w)
                    <option value="{{ $w->Wd_No }}" {{ $selectedWard==$w->Wd_No ? 'selected' : '' }}>{{ $w->Wd_Name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="{{ __('From') }}">
            <input type="date" name="date_to" value="{{ $dateTo }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="{{ __('To') }}">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">{{ __('Show') }}</button>
        </form>

        @if($requisitions->isEmpty())
            <p class="py-6 text-center text-sm text-slate-400">{{ __('No records for the selected period.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                            <th class="px-4 py-2">{{ __('Requisition No.') }}</th>
                            <th class="px-4 py-2">{{ __('Ward') }}</th>
                            <th class="px-4 py-2">{{ __('Date Ordered') }}</th>
                            <th class="px-4 py-2">{{ __('Date Received') }}</th>
                            <th class="px-4 py-2">{{ __('Items') }}</th>
                            <th class="px-4 py-2">{{ __('Total cost') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($requisitions as $req)
                            @php
                                $total=0; foreach($req->itemRequests as $ir) $total+=($ir->item?->CostPerUnit??0)*($ir->QtyReq??0);
                                foreach($req->drugRequests as $dr) $total+=($dr->drug?->CostPerUnit??0)*($dr->QtyReq??0);
                                $cnt=$req->itemRequests->count()+$req->drugRequests->count();
                            @endphp
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-800">{{ $req->Wd_Req_No }}</td>
                                <td class="px-4 py-2 text-slate-600">{{ $req->ward?->Wd_Name ?? $req->Wd_No }}</td>
                                <td class="px-4 py-2 text-slate-600">{{ $req->DateOrd?->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 text-slate-600">{{ $req->DateRecv?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-4 py-2 text-slate-600">{{ $cnt }}</td>
                                <td class="px-4 py-2 font-medium text-slate-800">{{ number_format($total,2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
        <h2 class="mb-2 text-sm font-semibold text-amber-800">{{ __('Stock below reorder level') }}</h2>
        <p class="mb-4 text-xs text-amber-700">{{ __('Items and drugs where QtyInStock ≤ ReorderLvl') }}</p>
        @if($lowItems->isEmpty() && $lowDrugs->isEmpty())
            <p class="text-sm text-amber-700">{{ __('All stock is above reorder level.') }}</p>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-amber-800">{{ __('Supplies') }}</h3>
                    @if($lowItems->isEmpty())
                        <p class="text-xs text-slate-500">{{ __('None') }}</p>
                    @else
                        <ul class="space-y-1 text-sm">
                            @foreach($lowItems as $it)
                                <li class="flex justify-between rounded-lg bg-white px-3 py-2"><span>{{ $it->Name }} ({{ $it->Item_No }})</span><span class="font-semibold text-amber-700">{{ $it->QtyInStock ?? 0 }} / {{ $it->ReorderLvl }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div>
                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-amber-800">{{ __('Pharmaceutical') }}</h3>
                    @if($lowDrugs->isEmpty())
                        <p class="text-xs text-slate-500">{{ __('None') }}</p>
                    @else
                        <ul class="space-y-1 text-sm">
                            @foreach($lowDrugs as $d)
                                <li class="flex justify-between rounded-lg bg-white px-3 py-2"><span>{{ $d->Name }} ({{ $d->Drug_No }})</span><span class="font-semibold text-amber-700">{{ $d->QtyInStock ?? 0 }} / {{ $d->ReorderLvl }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif
    </section>
</div>
@endsection
