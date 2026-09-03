@extends('layouts.app')

@section('title', __('Ward Requisitions'))

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Ward Requisitions') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Requisitions pending approval and preparation') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('requisitions.history') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('History') }}</a>
            <x-report-button :href="route('requisitions.report')" />
            <a href="{{ route('requisitions.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">+ {{ __('New Requisition') }}</a>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <select name="ward" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">{{ __('All wards') }}</option>
            @foreach ($wards as $w)
                <option value="{{ $w->Wd_No }}" {{ request('ward')==$w->Wd_No ? 'selected' : '' }}>{{ $w->Wd_Name }} ({{ $w->Wd_No }})</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">{{ __('All statuses') }}</option>
            <option value="Pending" {{ request('status')=='Pending' ? 'selected' : '' }}>Pending</option>
            <option value="Approved" {{ request('status')=='Approved' ? 'selected' : '' }}>Approved</option>
        </select>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-medium text-white">{{ __('Filter') }}</button>
        <a href="{{ route('requisitions.index') }}" class="rounded-lg bg-slate-100 px-4 py-1.5 text-sm text-slate-700">{{ __('Clear') }}</a>
    </form>

    @if ($requisitions->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
            <p class="text-sm font-medium text-slate-600">{{ __('No pending requisitions.') }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ __('New requisitions from wards will appear here.') }}</p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-3">{{ __('Requisition No.') }}</th>
                            <th class="px-5 py-3">{{ __('Ward') }}</th>
                            <th class="px-5 py-3">{{ __('Requested by') }}</th>
                            <th class="px-5 py-3">{{ __('Items') }}</th>
                            <th class="px-5 py-3">{{ __('Total cost') }}</th>
                            <th class="px-5 py-3">{{ __('Status') }}</th>
                            <th class="px-5 py-3">{{ __('Date Ordered') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($requisitions as $req)
                            @php
                                $itemCount = $req->itemRequests->count() + $req->drugRequests->count();
                                $total = 0;
                                foreach($req->itemRequests as $ir) $total += ($ir->item?->CostPerUnit ?? 0) * ($ir->QtyReq ?? 0);
                                foreach($req->drugRequests as $dr) $total += ($dr->drug?->CostPerUnit ?? 0) * ($dr->QtyReq ?? 0);
                            @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3 font-medium text-slate-900">{{ $req->Wd_Req_No }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $req->ward?->Wd_Name ?? $req->Wd_No }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $req->requester?->full_name ?? $req->Stf_No }}</td>
                                <td class="px-5 py-3"><span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">{{ $itemCount }} {{ Str::plural(__('item'), $itemCount) }}</span></td>
                                <td class="px-5 py-3 font-medium text-slate-700">{{ number_format($total, 2) }}</td>
                                <td class="px-5 py-3">
                                    @if ($req->status === 'Pending')
                                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Pending</span>
                                    @else
                                        <span class="rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-700">Approved</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $req->DateOrd?->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('requisitions.show', $req) }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $requisitions->links() }}</div>
    @endif
</div>
@endsection
