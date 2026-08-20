<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\CentralStock;
use App\Models\Patient;
use App\Models\Pharmaceutical;
use App\Models\StfRota;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        $totalBeds = Bed::count();
        $occupiedBeds = Bed::where('BedStatus', 'Occupied')->count();

        $appointmentsWeek = Appointment::whereBetween('ApptDate', [$weekStart, $weekEnd])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->keyBy('status');

        $trendDays = collect(range(13, 0, -1))
            ->map(fn (int $daysAgo) => $today->copy()->subDays($daysAgo)->toDateString());

        $patientsByDate = Patient::whereBetween('DateReg', [$trendDays->first(), $today])
            ->selectRaw('DateReg as date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $appointmentsByDate = Appointment::whereBetween('ApptDate', [$trendDays->first(), $today])
            ->selectRaw('ApptDate as date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        return view('dashboard.index', [
            'totalPatients' => Patient::count(),
            'newPatientsToday' => Patient::whereDate('DateReg', $today)->count(),
            'newPatientsThisWeek' => Patient::whereBetween('DateReg', [$weekStart, $weekEnd])->count(),
            'appointmentsToday' => Appointment::whereDate('ApptDate', $today)->count(),
            'appointmentsWeek' => $appointmentsWeek,
            'appointmentStatusLabels' => [
                'waiting list' => 'รอ (Waiting list)',
                'completed' => 'เสร็จสิ้น',
                'cancelled' => 'ยกเลิก',
            ],
            'bedOccupancy' => $totalBeds > 0 ? round($occupiedBeds / $totalBeds * 100, 1) : 0,
            'occupiedBeds' => $occupiedBeds,
            'totalBeds' => $totalBeds,
            'staffOnDuty' => StfRota::where('WkBegin', $weekStart->toDateString())
                ->distinct('Stf_No')
                ->count('Stf_No'),
            'reorderAlerts' => CentralStock::whereColumn('QtyInStock', '<=', 'ReorderLvl')
                ->orderBy('ReorderLvl')
                ->get(['Item_No', 'Name', 'QtyInStock', 'ReorderLvl']),
            'lowDrugs' => Pharmaceutical::whereColumn('QtyInStock', '<=', 'ReorderLvl')
                ->orderBy('ReorderLvl')
                ->get(['Drug_No', 'Name', 'QtyInStock', 'ReorderLvl']),
            'todayAppointments' => Appointment::with(['patient', 'consultant', 'room'])
                ->whereDate('ApptDate', $today)
                ->orderBy('ApptTime')
                ->get(),
            'trendDays' => $trendDays->values(),
            'trendLabels' => $trendDays->map(fn (string $date) => Carbon::parse($date)->format('d/m'))->values(),
            'patientTrend' => $trendDays->map(fn (string $date) => $patientsByDate[$date] ?? 0)->values(),
            'appointmentTrend' => $trendDays->map(fn (string $date) => $appointmentsByDate[$date] ?? 0)->values(),
            'chartData' => [
                'trendDays' => $trendDays->map(fn (string $date) => Carbon::parse($date)->format('d/m'))->values(),
                'patientTrend' => $trendDays->map(fn (string $date) => $patientsByDate[$date] ?? 0)->values(),
                'appointmentTrend' => $trendDays->map(fn (string $date) => $appointmentsByDate[$date] ?? 0)->values(),
            ],
        ]);
    }
}
