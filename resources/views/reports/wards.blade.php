@extends('layouts.report')

@section('title', __('Wards Report'))
@section('report_subtitle', __('Ward Report'))
@section('report_context', __('All wards'))
@section('report_reference', __('Wards') . ' ' . $wards->count())

@section('content')
<h2 style="margin:0 0 8px;font-size:13pt;color:#0f172a;">{{ __('Ward Bed Status') }} — {{ $wards->count() }} {{ __('wards') }}</h2>
<table>
    <thead><tr><th>{{ __('Ward') }}</th><th>{{ __('Location') }}</th><th>{{ __('Ext') }}</th><th style="text-align:right;">{{ __('Total') }}</th><th style="text-align:right;">{{ __('Available') }}</th><th style="text-align:right;">{{ __('Occupied') }}</th></tr></thead>
    <tbody>
        @foreach($wards as $ward)
            @php $s = $bedStats[$ward->Wd_No] ?? null; @endphp
            <tr>
                <td><strong>{{ $ward->Wd_Name }}</strong><br><span style="font-family:monospace;color:#64748b;">{{ $ward->Wd_No }}</span></td>
                <td>{{ $ward->Location }}</td>
                <td>{{ $ward->TelExtension }}</td>
                <td style="text-align:right;">{{ $ward->TotalBeds }}</td>
                <td style="text-align:right;color:#059669;font-weight:600;">{{ $s->available_beds ?? 0 }}</td>
                <td style="text-align:right;color:#dc2626;">{{ $s->occupied_beds ?? 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
