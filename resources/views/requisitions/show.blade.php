@extends('layouts.app')

@section('title', $requisition->Wd_Req_No)

@section('content')
<div class="mx-auto max-w-4xl">
    <a href="{{ route('requisitions.index') }}" class="mb-4 inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-slate-700">← {{ __('Back to requisitions') }}</a>

    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-900">{{ __('Requisition') }} {{ $requisition->Wd_Req_No }}</h1>
                <p class="mt-1 text-xs text-slate-500">
                    {{ __('Ward') }}: <span class="font-semibold text-slate-700">{{ $requisition->ward?->Wd_Name ?? $requisition->Wd_No }}</span>
                    · {{ __('Requested by') }}: {{ $requisition->requester?->full_name ?? $requisition->Stf_No }}
                    · {{ __('Date Ordered') }}: {{ $requisition->DateOrd?->format('d/m/Y') ?? '—' }}
                </p>
                <p class="mt-1 text-xs">
                    {{ __('Status') }}:
                    @if($requisition->status==='Pending')<span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Pending</span>
                    @elseif($requisition->status==='Approved')<span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700">Approved</span>
                    @else<span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Completed</span>
                    @endif
                </p>
                @if($requisition->status==='Completed')
                    <p class="mt-1 text-xs text-slate-500">{{ __('Received by') }}: {{ $requisition->receiver?->full_name ?? $requisition->Received_By }} · {{ $requisition->DateRecv?->format('d/m/Y') }}</p>
                @endif
            </div>
            <div class="flex gap-2">
                @if($requisition->status==='Pending')
                    <a href="{{ route('requisitions.edit', $requisition) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">{{ __('Edit') }}</a>
                    <form method="POST" action="{{ route('requisitions.destroy', $requisition) }}" onsubmit="return confirm('{{ __('Delete this requisition?') }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">{{ __('Delete') }}</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                        <th class="px-4 py-2">{{ __('Item / Drug') }}</th>
                        <th class="px-4 py-2">{{ __('Type') }}</th>
                        <th class="px-4 py-2">{{ __('Description / Dosage') }}</th>
                        <th class="px-4 py-2">{{ __('Cost / unit') }}</th>
                        <th class="px-4 py-2">{{ __('Qty required') }}</th>
                        <th class="px-4 py-2">{{ __('Subtotal') }}</th>
                        <th class="px-4 py-2">{{ __('Stock') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($requisition->itemRequests as $ir)
                        @php $cost = $ir->item?->CostPerUnit ?? 0; $sub = $cost * ($ir->QtyReq ?? 0); $low = ($ir->item?->QtyInStock ?? 0) <= ($ir->item?->ReorderLvl ?? PHP_INT_MAX); @endphp
                        <tr>
                            <td class="px-4 py-2 font-medium text-slate-800">{{ $ir->item?->Name ?? $ir->Item_No }} <span class="text-xs text-slate-400">({{ $ir->Item_No }})</span></td>
                            <td class="px-4 py-2 text-slate-600">{{ $ir->item?->ItemType ?? '—' }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $ir->item?->Description ?? '—' }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ number_format($cost,2) }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $ir->QtyReq }}</td>
                            <td class="px-4 py-2 font-medium text-slate-800">{{ number_format($sub,2) }}</td>
                            <td class="px-4 py-2">
                                <span class="text-slate-600">{{ $ir->item?->QtyInStock ?? 0 }}</span>
                                @if($low)<span class="ml-1 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700">{{ __('Low') }}</span>@endif
                            </td>
                        </tr>
                    @endforeach
                    @foreach($requisition->drugRequests as $dr)
                        @php $cost = $dr->drug?->CostPerUnit ?? 0; $sub = $cost * ($dr->QtyReq ?? 0); $low = ($dr->drug?->QtyInStock ?? 0) <= ($dr->drug?->ReorderLvl ?? PHP_INT_MAX); @endphp
                        <tr>
                            <td class="px-4 py-2 font-medium text-slate-800">{{ $dr->drug?->Name ?? $dr->Drug_No }} <span class="text-xs text-slate-400">({{ $dr->Drug_No }})</span></td>
                            <td class="px-4 py-2 text-slate-600">Pharmaceutical</td>
                            <td class="px-4 py-2 text-slate-600">{{ $dr->drug?->Dosage ?? '' }} {{ $dr->drug?->AdminMethod ? '· '.$dr->drug->AdminMethod : '' }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ number_format($cost,2) }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $dr->QtyReq }}</td>
                            <td class="px-4 py-2 font-medium text-slate-800">{{ number_format($sub,2) }}</td>
                            <td class="px-4 py-2">
                                <span class="text-slate-600">{{ $dr->drug?->QtyInStock ?? 0 }}</span>
                                @if($low)<span class="ml-1 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-700">{{ __('Low') }}</span>@endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50">
                    <tr class="font-semibold text-slate-900">
                        <td colspan="5" class="px-4 py-2 text-right">{{ __('Total cost') }}</td>
                        <td class="px-4 py-2">{{ number_format($totalCost,2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($requisition->status==='Pending')
            <div class="mt-6 rounded-xl border border-sky-200 bg-sky-50 p-4">
                <h3 class="text-sm font-bold text-sky-800">{{ __('Approve & Prepare') }}</h3>
                <p class="mt-1 text-xs text-sky-700">{{ __('This will deduct the quantities from central stock. Blocked if stock is insufficient. Low stock after deduction will be flagged.') }}</p>
                <form method="POST" action="{{ route('requisitions.approve', $requisition) }}" class="mt-3">
                    @csrf
                    <button type="submit" onclick="return confirm('{{ __('Approve and deduct stock?') }}')" class="w-full rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-700">{{ __('Approve & Prepare') }}</button>
                </form>
            </div>
        @elseif($requisition->status==='Approved')
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <h3 class="text-sm font-bold text-emerald-800">{{ __('Confirm receipt at ward') }}</h3>
                <p class="mt-1 text-xs text-emerald-700">{{ __('When goods arrive, confirm receipt. This moves the requisition to history.') }}</p>
                <form method="POST" action="{{ route('requisitions.receive', $requisition) }}" class="mt-3 grid gap-3 sm:grid-cols-3">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium text-emerald-800">{{ __('Received by') }} *</label>
                        <select name="Received_By" required class="w-full rounded-lg border border-emerald-200 bg-white px-3 py-2 text-sm">
                            <option value="">{{ __('Select Staff') }}</option>
                            @foreach(\App\Models\Stf::orderBy('LastName')->get() as $s)
                                <option value="{{ $s->Stf_No }}">{{ $s->full_name }} ({{ $s->Stf_No }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-emerald-800">{{ __('Date Received') }} *</label>
                        <input type="date" name="DateRecv" value="{{ now()->toDateString() }}" required class="w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">{{ __('Confirm receipt') }}</button>
                    </div>
                </form>
            </div>
        @else
            <p class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700">{{ __('This requisition is completed and archived in history.') }}</p>
        @endif
    </div>
</div>
@endsection
