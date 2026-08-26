@extends('layouts.app')

@section('title', __('Requisition History'))

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ __('Requisition History') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Completed requisitions — delivered and received') }}</p>
        </div>
        <a href="{{ route('requisitions.index') }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Back to queue') }}</a>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <select name="ward" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
            <option value="">{{ __('All wards') }}</option>
            @foreach($wards as $w)
                <option value="{{ $w->Wd_No }}" {{ request('ward')==$w->Wd_No ? 'selected' : '' }}>{{ $w->Wd_Name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-medium text-white">{{ __('Filter') }}</button>
    </form>

    @if($requisitions->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
            <p class="text-sm text-slate-500">{{ __('No completed requisitions yet.') }}</p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-3">{{ __('Requisition No.') }}</th>
                            <th class="px-5 py-3">{{ __('Ward') }}</th>
                            <th class="px-5 py-3">{{ __('Date Ordered') }}</th>
                            <th class="px-5 py-3">{{ __('Date Received') }}</th>
                            <th class="px-5 py-3">{{ __('Received by') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($requisitions as $req)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-900">{{ $req->Wd_Req_No }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $req->ward?->Wd_Name ?? $req->Wd_No }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $req->DateOrd?->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $req->DateRecv?->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $req->receiver?->full_name ?? $req->Received_By }}</td>
                                <td class="px-5 py-3 text-right"><a href="{{ route('requisitions.show', $req) }}" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white">{{ __('View') }}</a></td>
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
