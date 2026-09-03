@extends('layouts.report')

@section('title', __('Patient List Report'))
@section('report_subtitle', __('Patient Report'))
@section('report_context', __('Patient list'))
@section('report_reference', __('Filters') . ': ' . (request('search') ?: __('All')) . ' | ' . now()->format('Ymd-His'))

@section('content')
<h2 style="margin:0 0 8px;font-size:13pt;color:#0f172a;">{{ __('Patient List') }} — {{ $patients->count() }} {{ __('records') }}</h2>
@if(request('search') || request('clinic_no'))
<p style="font-size:9pt;color:#64748b;">{{ __('Filtered by') }}: {{ request('search') ? __('Search').'="'.request('search').'" ' : '' }}{{ request('clinic_no') ? __('Doctor').'='.request('clinic_no') : '' }}</p>
@endif
<table>
    <thead>
        <tr>
            <th>{{ __('Patient No.') }}</th>
            <th>{{ __('Name') }}</th>
            <th>{{ __('DOB') }}</th>
            <th>{{ __('Sex') }}</th>
            <th>{{ __('Phone') }}</th>
            <th>{{ __('Local Doctor') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($patients as $p)
            <tr>
                <td style="font-family:monospace;">{{ $p->Pt_No }}</td>
                <td><strong>{{ $p->full_name }}</strong></td>
                <td>{{ $p->DOB?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $p->Sex ?? '-' }}</td>
                <td>{{ $p->TelNo ?? '-' }}</td>
                <td>{{ $p->localDoctor?->full_name ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:20px;">{{ __('No patients found.') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p style="font-size:8pt;color:#64748b;margin-top:10px;">{{ __('Total') }}: {{ $patients->count() }} | {{ __('Generated on') }} {{ now()->format('d/m/Y H:i') }}</p>
@endsection
