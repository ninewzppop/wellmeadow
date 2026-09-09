@extends('layouts.report')

@section('title', __('Consult Report') . ' ' . $room->Room_No)
@section('report_subtitle', __('Consult Report'))
@section('report_context', ($room->RoomName ?? __('Room')) . ' — ' . $reportDate->format('d/m/Y'))
@section('report_reference', __('Room') . ' ' . $room->Room_No . ' | ' . __('Date') . ' ' . $reportDate->format('d/m/Y'))

@section('content')
<h2 style="margin:0 0 4px;font-size:14pt;color:#0f172a;">{{ $room->RoomName ?? __('Consultation Room') }} <span style="font-weight:400;color:#64748b;">{{ $room->Room_No }}</span></h2>
<p style="margin:0 0 12px;font-size:9pt;color:#64748b;">{{ $room->Location ?? '' }} · {{ __('Date') }}: {{ $reportDate->format('d/m/Y') }} · {{ __('Queue') }} {{ $queue->count() }} + {{ __('Finished') }} {{ $finished->count() }}</p>

<h3 style="font-size:10pt;color:#0f172a;margin:12px 0 6px;">{{ __('Patient queue') }} ({{ $queue->count() }})</h3>
@if($queue->isEmpty())
<p style="color:#94a3b8;font-size:9pt;">{{ __('No patients in queue.') }}</p>
@else
<table>
    <thead><tr><th>#</th><th>{{ __('Time') }}</th><th>{{ __('Patient') }}</th><th>{{ __('HN') }}</th><th>{{ __('Consultant') }}</th><th>{{ __('Status') }}</th><th>{{ __('Allergies') }}</th></tr></thead>
    <tbody>
        @foreach($queue as $i => $appt)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $appt->ApptTime?->format('H:i') ?? '-' }}<br><span style="font-size:8pt;color:#94a3b8;">{{ $appt->Appt_No }}</span></td>
                <td><strong><x-patient-link :patient="$appt->patient" /></strong></td>
                <td style="font-family:monospace;">{{ $appt->patient?->Pt_No ?? '-' }}</td>
                <td>{{ $appt->consultant?->full_name ?? '-' }}</td>
                <td>{{ $appt->status }}</td>
                <td style="font-size:8pt;">
                    @if($appt->patient && $appt->patient->allergies->isNotEmpty())
                        <span style="color:#dc2626;">{{ $appt->patient->allergies->map(fn($a)=>$a->Allergy_Name??$a->drug?->Name)->implode(', ') }}</span>
                    @else
                        <span style="color:#059669;">{{ __('None') }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

@if($finished->isNotEmpty())
<h3 style="font-size:10pt;color:#475569;margin:16px 0 6px;">{{ __('Finished today') }} ({{ $finished->count() }})</h3>
<table>
    <thead><tr><th>{{ __('Time') }}</th><th>{{ __('Patient') }}</th><th>{{ __('Consultant') }}</th><th>{{ __('Outcome') }}</th></tr></thead>
    <tbody>
        @foreach($finished as $appt)
            <tr><td>{{ $appt->ApptTime?->format('H:i') ?? '-' }}</td><td><x-patient-link :patient="$appt->patient" /></td><td>{{ $appt->consultant?->full_name ?? '-' }}</td><td>{{ $appt->status }}</td></tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection
