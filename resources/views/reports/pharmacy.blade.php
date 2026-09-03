@extends('layouts.report')

@section('title', __('Pharmacy Report'))
@section('report_subtitle', __('Pharmacy Report'))
@section('report_context', __('Pharmacy') . ' — ' . $summary['total'] . ' ' . __('items'))
@section('report_reference', __('Filters') . ': ' . (request('search') ?: __('All')) . (request('status') ? ' | '.request('status') : '') . (request('expiry') ? ' | '.request('expiry') : ''))

@section('content')
<h2 style="margin:0 0 8px;font-size:13pt;color:#0f172a;">{{ __('Pharmacy Summary') }}</h2>
<table style="margin-bottom:12px;width:auto;">
    <tr>
        <th>{{ __('Total items') }}</th><td>{{ $summary['total'] }}</td>
        <th>{{ __('Low stock') }}</th><td>{{ $summary['low'] }}</td>
        <th>{{ __('Out of stock') }}</th><td>{{ $summary['out'] }}</td>
        <th>{{ __('Expired') }}</th><td>{{ $summary['expired'] }}</td>
        <th>{{ __('Near expiry') }}</th><td>{{ $summary['nearExpiry'] }}</td>
        <th>{{ __('Total value') }}</th><td style="text-align:right;">{{ number_format($summary['totalValue'], 2) }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>{{ __('Drug No') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Dosage') }}</th>
            <th>{{ __('Method') }}</th>
            <th style="text-align:right;">{{ __('In stock') }}</th>
            <th style="text-align:right;">{{ __('Reorder') }}</th>
            <th>{{ __('Expiry date') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $drug)
            <tr>
                <td style="font-family:monospace;">{{ $drug->Drug_No }}</td>
                <td><strong>{{ $drug->Name }}</strong><br><span style="font-size:8pt;color:#94a3b8;">{{ $drug->Description }}</span></td>
                <td>{{ $drug->Dosage ?? '-' }}</td>
                <td>{{ $drug->AdminMethod ?? '-' }}</td>
                <td style="text-align:right;font-weight:600;">{{ $drug->QtyInStock ?? 0 }}</td>
                <td style="text-align:right;">{{ $drug->ReorderLvl ?? '-' }}</td>
                <td>
                    @if($drug->ExpiryDate)
                        <span style="color:{{ $drug->expiry_status==='expired' ? '#be123c' : ($drug->expiry_status==='near-expiry' ? '#c2410c' : '#334155') }};font-weight:{{ $drug->expiry_status==='expired' ? '600' : '400' }};">
                            {{ $drug->ExpiryDate->format('d/m/Y') }}
                        </span>
                        <span style="font-size:7pt;color:#94a3b8;">({{ $drug->expiry_status }})</span>
                    @else
                        <span style="color:#94a3b8;">-</span>
                    @endif
                </td>
                <td>{{ $drug->stock_status }}</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;color:#94a3b8;">{{ __('No pharmaceutical records.') }}</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
