@extends('layouts.report')

@section('title', __('Patient Report') . ' ' . $patient->Pt_No)
@section('report_subtitle', __('Patient Report'))
@section('report_context', $patient->full_name)
@section('report_reference', __('HN') . ' ' . $patient->Pt_No . ' | ' . $patient->DOB?->format('d/m/Y'))

@section('content')
<h2 style="margin:0 0 12px;font-size:14pt;color:#0f172a;">{{ $patient->full_name }} <span style="font-weight:400;color:#64748b;font-size:10pt;">{{ $patient->Pt_No }}</span></h2>

<table style="margin-bottom:14px;">
    <tr><th style="width:28%;">{{ __('DOB') }}</th><td>{{ $patient->DOB?->format('d/m/Y') ?? '-' }}</td><th>{{ __('Sex') }}</th><td>{{ $patient->Sex ?? '-' }}</td></tr>
    <tr><th>{{ __('Phone') }}</th><td>{{ $patient->TelNo ?? '-' }}</td><th>{{ __('Date Registered') }}</th><td>{{ $patient->DateReg?->format('d/m/Y') ?? '-' }}</td></tr>
    <tr><th>{{ __('Address') }}</th><td colspan="3">{{ $patient->Address ?? '-' }}</td></tr>
    <tr><th>{{ __('Primary Care Doctor') }}</th><td colspan="3">{{ $patient->localDoctor?->full_name ?? '-' }} {{ $patient->localDoctor ? '('.$patient->Clinic_No.')' : '' }}</td></tr>
    <tr><th>{{ __('Marital Status') }}</th><td colspan="3">{{ $patient->MaritalStat ?? '-' }}</td></tr>
</table>

@if($patient->allergies->isNotEmpty())
<h3 style="font-size:10pt;color:#b91c1c;margin:16px 0 6px;">{{ __('Allergies') }} ({{ $patient->allergies->count() }})</h3>
<table>
    <thead><tr><th>{{ __('Allergen') }}</th><th>{{ __('Reaction') }}</th><th>{{ __('Severity') }}</th><th>{{ __('Diagnosed') }}</th></tr></thead>
    <tbody>
        @foreach($patient->allergies as $a)
            <tr><td>{{ $a->Allergy_Name ?? $a->drug?->Name ?? $a->Drug_No }}</td><td>{{ $a->Reaction }}</td><td>{{ $a->Severity }}</td><td>{{ $a->DiagDate?->format('d/m/Y') }}</td></tr>
        @endforeach
    </tbody>
</table>
@endif

<h3 style="font-size:10pt;color:#0f172a;margin:16px 0 6px;">{{ __('Medication History') }} ({{ $patient->medications->count() }})</h3>
@if($patient->medications->isEmpty())
<p style="color:#94a3b8;font-size:9pt;">{{ __('No medication history.') }}</p>
@else
<table>
    <thead><tr><th>{{ __('Drug') }}</th><th>{{ __('Units/day') }}</th><th>{{ __('Method') }}</th><th>{{ __('Period') }}</th><th>{{ __('Prescriber') }}</th></tr></thead>
    <tbody>
        @foreach($patient->medications->sortByDesc('StartDate') as $m)
            <tr><td>{{ $m->drug?->Name ?? $m->Drug_No }}</td><td style="text-align:right;">{{ $m->UnitsPerDay }}</td><td>{{ $m->AdminMethod }}</td><td>{{ $m->StartDate?->format('d/m/Y') }} → {{ $m->FinishDate?->format('d/m/Y') }}</td><td>{{ $m->staff?->full_name ?? $m->Stf_No ?? '-' }}</td></tr>
        @endforeach
    </tbody>
</table>
@endif

<h3 style="font-size:10pt;color:#0f172a;margin:16px 0 6px;">{{ __('Appointments') }} ({{ $patient->appointments->count() }})</h3>
@if($patient->appointments->isEmpty())
<p style="color:#94a3b8;font-size:9pt;">{{ __('No appointments') }}</p>
@else
<table>
    <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Room') }}</th><th>{{ __('Consultant') }}</th><th>{{ __('Status') }}</th></tr></thead>
    <tbody>
        @foreach($patient->appointments->sortByDesc('ApptDate') as $a)
            <tr><td>{{ $a->ApptDate?->format('d/m/Y') }} {{ $a->ApptTime?->format('H:i') }}</td><td>{{ $a->room?->RoomName ?? $a->Room_No }}</td><td>{{ $a->consultant?->full_name ?? '-' }}</td><td>{{ $a->status }}</td></tr>
        @endforeach
    </tbody>
</table>
@endif

@if($patient->inPatients->isNotEmpty())
<h3 style="font-size:10pt;color:#0f172a;margin:16px 0 6px;">{{ __('Admissions') }}</h3>
<table>
    <thead><tr><th>{{ __('In-Pt No') }}</th><th>{{ __('Bed / Ward') }}</th><th>{{ __('Admitted') }}</th><th>{{ __('Discharged') }}</th></tr></thead>
    <tbody>
        @foreach($patient->inPatients as $ip)
            <tr><td style="font-family:monospace;">{{ $ip->In_Pt_No }}</td><td>{{ $ip->bed?->ward?->Wd_Name ?? '-' }} / {{ $ip->Bed_No ?? '-' }}</td><td>{{ $ip->DatePlaced?->format('d/m/Y') ?? '-' }}</td><td>{{ $ip->ActDateLeft?->format('d/m/Y') ?? '-' }}</td></tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection
