@extends('layouts.report')

@section('title', __('Stock Report'))
@section('report_subtitle', __('Stock Report'))
@section('report_context', __('Central Stock') . ' — ' . $summary['total'] . ' ' . __('items'))
@section('report_reference', __('Filters') . ': ' . (request('search') ?: __('All')) . (request('type') ? ' | '.request('type') : ''))

@section('content')
<h2 style="margin:0 0 8px;font-size:13pt;color:#0f172a;">{{ __('Central Stock Summary') }}</h2>
<table style="margin-bottom:12px;width:auto;">
    <tr><th>{{ __('Total items') }}</th><td>{{ $summary['total'] }}</td><th>{{ __('Low stock') }}</th><td>{{ $summary['low'] }}</td><th>{{ __('Out of stock') }}</th><td>{{ $summary['out'] }}</td><th>{{ __('Total value') }}</th><td style="text-align:right;">{{ number_format($summary['totalValue'], 2) }}</td></tr>
</table>

<table>
    <thead>
        <tr>
            <th>{{ __('Item No') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Category') }}</th>
            <th style="text-align:right;">{{ __('In stock') }}</th>
            <th style="text-align:right;">{{ __('Reorder') }}</th>
            <th>{{ __('Supplier') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
            <tr>
                <td style="font-family:monospace;">{{ $item->Item_No }}</td>
                <td><strong>{{ $item->Name }}</strong><br><span style="font-size:8pt;color:#94a3b8;">{{ $item->Description }}</span></td>
                <td>{{ $item->ItemType }}</td>
                <td style="text-align:right;font-weight:600;">{{ $item->QtyInStock ?? 0 }}</td>
                <td style="text-align:right;">{{ $item->ReorderLvl ?? '-' }}</td>
                <td>{{ $item->supplier?->Name ?? '-' }}</td>
                <td>{{ $item->stock_status }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;color:#94a3b8;">{{ __('No stock items.') }}</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
