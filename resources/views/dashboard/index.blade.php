@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    <h1 class="mb-6 text-2xl font-bold text-slate-800">{{ __('Dashboard') }}</h1>

    <script type="application/json" id="dashboard-chart-data">
        @json($chartData)
    </script>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Total patients') }}</p>
            <p class="mt-1 text-3xl font-bold text-slate-800">{{ number_format($totalPatients) }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ __('Registered this week :count people', ['count' => $newPatientsThisWeek]) }}</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('New patients today') }}</p>
            <p class="mt-1 text-3xl font-bold text-sky-700">{{ number_format($newPatientsToday) }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ __('From the Patient table (DateReg)') }}</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Appointments today') }}</p>
            <p class="mt-1 text-3xl font-bold text-sky-700">{{ number_format($appointmentsToday) }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ __('From the Appointment table (ApptDate)') }}</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Staff on duty this week') }}</p>
            <p class="mt-1 text-3xl font-bold text-slate-800">{{ number_format($staffOnDuty) }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ __('From the StfRota table this week') }}</p>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Bed occupancy') }}</p>
            <p class="mt-1 text-3xl font-bold text-emerald-700">{{ $bedOccupancy }}%</p>
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-200">
                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $bedOccupancy }}%"></div>
            </div>
            <p class="mt-2 text-xs text-slate-400">{{ __(':occupied / :total beds (Bed.BedStatus)', ['occupied' => $occupiedBeds, 'total' => $totalBeds]) }}</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ __('Appointments this week (:count)', ['count' => $appointmentsWeek->sum('total')]) }}</p>
            <div class="mt-3 space-y-2">
                @foreach ($appointmentStatusLabels as $status => $label)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-600">{{ $label }}</span>
                        <span class="font-semibold text-slate-800">{{ $appointmentsWeek[$status]->total ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="mb-4 text-sm font-medium text-slate-500">{{ __('New patients per day — 14 days') }}</p>
            <div class="h-64"><canvas id="patients-trend"></canvas></div>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="mb-4 text-sm font-medium text-slate-500">{{ __('Appointments per day — 14 days') }}</p>
            <div class="h-64"><canvas id="appointments-trend"></canvas></div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="mb-4 text-sm font-medium text-slate-500">{{ __('Appointments today') }}</p>
            @if ($todayAppointments->isEmpty())
                <p class="text-sm text-slate-400">{{ __('No appointments today') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-400">
                                <th class="py-2 pr-3">{{ __('Time') }}</th>
                                <th class="py-2 pr-3">{{ __('Patient') }}</th>
                                <th class="py-2 pr-3">{{ __('Doctor') }}</th>
                                <th class="py-2 pr-3">{{ __('Room') }}</th>
                                <th class="py-2">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($todayAppointments as $appointment)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2 pr-3">{{ $appointment->ApptTime?->format('H:i') ?? '—' }}</td>
                                    <td class="py-2 pr-3">{{ $appointment->patient?->full_name ?? $appointment->Pt_No }}</td>
                                    <td class="py-2 pr-3">{{ $appointment->consultant?->full_name ?? '—' }}</td>
                                    <td class="py-2 pr-3">{{ $appointment->room?->RoomName ?? $appointment->Room_No ?? '—' }}</td>
                                    <td class="py-2">
                                        @php $status = $appointment->status; @endphp
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                            {{ $status === 'completed' ? 'bg-green-100 text-green-700' : ($status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                            {{ $appointmentStatusLabels[$status] ?? $status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="mb-4 text-sm font-medium text-slate-500">{{ __('Stock/medication near reorder level') }}</p>
            @if ($reorderAlerts->isEmpty() && $lowDrugs->isEmpty())
                <p class="text-sm text-slate-400">{{ __('No items near the reorder level') }}</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($reorderAlerts as $item)
                        <li class="flex items-center justify-between rounded bg-red-50 px-3 py-2">
                            <span class="text-slate-700">{{ $item->Name }}</span>
                            <span class="text-xs text-red-600">{{ __('In stock :qty / reorder at :lvl', ['qty' => $item->QtyInStock, 'lvl' => $item->ReorderLvl]) }}</span>
                        </li>
                    @endforeach
                    @foreach ($lowDrugs as $drug)
                        <li class="flex items-center justify-between rounded bg-red-50 px-3 py-2">
                            <span class="text-slate-700">{{ $drug->Name }}</span>
                            <span class="text-xs text-red-600">{{ __('In stock :qty / reorder at :lvl', ['qty' => $drug->QtyInStock, 'lvl' => $drug->ReorderLvl]) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/dashboard.js')
@endpush