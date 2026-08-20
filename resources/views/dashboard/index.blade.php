@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="mb-6 text-2xl font-bold text-slate-800">Dashboard</h1>

    <script type="application/json" id="dashboard-chart-data">
        @json($chartData)
    </script>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">ผู้ป่วยทั้งหมด</p>
            <p class="mt-1 text-3xl font-bold text-slate-800">{{ number_format($totalPatients) }}</p>
            <p class="mt-1 text-xs text-slate-400">ลงทะเบียนสัปดาห์นี้ {{ $newPatientsThisWeek }} คน</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">ผู้ป่วยใหม่วันนี้</p>
            <p class="mt-1 text-3xl font-bold text-sky-700">{{ number_format($newPatientsToday) }}</p>
            <p class="mt-1 text-xs text-slate-400">จากตาราง Patient (DateReg)</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">นัดหมายวันนี้</p>
            <p class="mt-1 text-3xl font-bold text-sky-700">{{ number_format($appointmentsToday) }}</p>
            <p class="mt-1 text-xs text-slate-400">จากตาราง Appointment (ApptDate)</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">บุคลากรประจำสัปดาห์</p>
            <p class="mt-1 text-3xl font-bold text-slate-800">{{ number_format($staffOnDuty) }}</p>
            <p class="mt-1 text-xs text-slate-400">จากตาราง StfRota สัปดาห์นี้</p>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">อัตราการครองเตียง</p>
            <p class="mt-1 text-3xl font-bold text-emerald-700">{{ $bedOccupancy }}%</p>
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-200">
                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $bedOccupancy }}%"></div>
            </div>
            <p class="mt-2 text-xs text-slate-400">{{ $occupiedBeds }} / {{ $totalBeds }} เตียง (Bed.BedStatus)</p>
        </div>

        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">นัดหมายสัปดาห์นี้ ({{ $appointmentsWeek->sum('total') }})</p>
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
            <p class="mb-4 text-sm font-medium text-slate-500">ผู้ป่วยใหม่ต่อวัน — 14 วัน</p>
            <div class="h-64"><canvas id="patients-trend"></canvas></div>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="mb-4 text-sm font-medium text-slate-500">นัดหมายต่อวัน — 14 วัน</p>
            <div class="h-64"><canvas id="appointments-trend"></canvas></div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="mb-4 text-sm font-medium text-slate-500">นัดหมายวันนี้</p>
            @if ($todayAppointments->isEmpty())
                <p class="text-sm text-slate-400">ไม่มีนัดหมายวันนี้</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-400">
                                <th class="py-2 pr-3">เวลา</th>
                                <th class="py-2 pr-3">ผู้ป่วย</th>
                                <th class="py-2 pr-3">แพทย์</th>
                                <th class="py-2 pr-3">ห้อง</th>
                                <th class="py-2">สถานะ</th>
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
            <p class="mb-4 text-sm font-medium text-slate-500">สต็อก/ยาใกล้ระดับสั่งซื้อ</p>
            @if ($reorderAlerts->isEmpty() && $lowDrugs->isEmpty())
                <p class="text-sm text-slate-400">ไม่มีรายการที่ใกล้ระดับสั่งซื้อ</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($reorderAlerts as $item)
                        <li class="flex items-center justify-between rounded bg-red-50 px-3 py-2">
                            <span class="text-slate-700">{{ $item->Name }}</span>
                            <span class="text-xs text-red-600">คงเหลือ {{ $item->QtyInStock }} / ขั้นต่ำ {{ $item->ReorderLvl }}</span>
                        </li>
                    @endforeach
                    @foreach ($lowDrugs as $drug)
                        <li class="flex items-center justify-between rounded bg-red-50 px-3 py-2">
                            <span class="text-slate-700">{{ $drug->Name }}</span>
                            <span class="text-xs text-red-600">คงเหลือ {{ $drug->QtyInStock }} / ขั้นต่ำ {{ $drug->ReorderLvl }}</span>
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