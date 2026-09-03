@extends('layouts.report')

@section('title', __('Ward Report') . ' ' . $ward->Wd_No)
@section('report_subtitle', __('Ward Report'))
@section('report_context', $ward->Wd_Name . ' (' . $ward->Wd_No . ')')
@section('report_reference', __('Ward') . ' ' . $ward->Wd_No . ' | ' . __('Beds') . ' ' . $available . '/' . ($beds->count() ?: $ward->TotalBeds))

@section('content')
<h2 style="margin:0 0 4px;font-size:14pt;color:#0f172a;">{{ $ward->Wd_Name }} <span style="font-weight:400;color:#64748b;">{{ $ward->Wd_No }}</span></h2>
<p style="margin:0 0 12px;font-size:9pt;color:#64748b;">{{ $ward->Location }} · {{ __('Ext') }} {{ $ward->TelExtension }} · {{ __('Total beds') }}: {{ $ward->TotalBeds }} · {{ __('Available') }}: {{ $available }} · {{ __('Occupied') }}: {{ $occupied }}</p>

<h3 style="font-size:10pt;color:#0f172a;margin:12px 0 6px;">{{ __('Beds') }} ({{ $beds->count() }})</h3>
<table>
    <thead><tr><th>{{ __('Bed No') }}</th><th>{{ __('Status') }}</th></tr></thead>
    <tbody>
        @foreach($beds as $bed)
            <tr><td style="font-family:monospace;">{{ $bed->Bed_No }}</td><td><span class="badge" style="background:{{ $bed->BedStatus==='Available' ? '#dcfce7' : '#fee2e2' }};color:{{ $bed->BedStatus==='Available' ? '#166534' : '#991b1b' }};">{{ $bed->BedStatus }}</span></td></tr>
        @endforeach
    </tbody>
</table>

<h3 style="font-size:10pt;color:#0f172a;margin:16px 0 6px;">{{ __('All wards overview') }}</h3>
<table>
    <thead><tr><th>{{ __('Ward') }}</th><th>{{ __('Location') }}</th><th style="text-align:right;">{{ __('Available') }}</th><th style="text-align:right;">{{ __('Occupied') }}</th><th style="text-align:right;">{{ __('Total') }}</th></tr></thead>
    <tbody>
        @foreach($allWards as $w)
            @php $s = $bedStats[$w->Wd_No] ?? null; @endphp
            <tr><td><strong>{{ $w->Wd_Name }}</strong> <span style="color:#94a3b8;">{{ $w->Wd_No }}</span></td><td>{{ $w->Location }}</td><td style="text-align:right;">{{ $s->available_beds ?? 0 }}</td><td style="text-align:right;">{{ $s->occupied_beds ?? 0 }}</td><td style="text-align:right;">{{ $w->TotalBeds }}</td></tr>
        @endforeach
    </tbody>
</table>
@endsection
