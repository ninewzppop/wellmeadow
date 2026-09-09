<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\CentralStock;
use App\Models\InPatient;
use App\Models\MedicationOrder;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\Pharmaceutical;
use App\Models\StfRota;
use App\Models\Wardrequisition;
use App\Models\Wd;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public const STAFF_LOW_THRESHOLD = 2;

    public function index(Request $request): View
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        // Ward filter (Group A vs Group B)
        $selectedWard = $request->query('ward');
        $wardExists = $selectedWard ? Wd::where('Wd_No', $selectedWard)->exists() : false;
        $wardFilter = $wardExists ? $selectedWard : null;

        // Role scope: ward-bound roles are locked to their own ward dashboard.
        $user = $request->user();
        $isClinician = $user->isClinician();
        if ($user->hasRole(['charge_nurse', 'doctor', 'consultant', 'senior_nurse', 'staff_nurse', 'auxiliary'])
            && ! $user->managesAllWards()
            && $user->wardNo() !== null) {
            $wardFilter = $user->wardNo();
        }

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

        // Ward filter source (for dropdown) — natural sort by Wd_No numeric part (WD01, WD02, WD10 ...)
        $wardsForFilter = Wd::orderBy('Wd_No')->get(['Wd_No', 'Wd_Name'])
            ->sortBy(fn (Wd $w) => (int) preg_replace('/\D/', '', $w->Wd_No))
            ->values();
        $wardBedStatsQuery = Wd::orderBy('Wd_No');
        if ($wardFilter) {
            $wardBedStatsQuery->where('Wd_No', $wardFilter);
        }
        $wardsForWidget = $wardBedStatsQuery->get()
            ->sortBy(fn (Wd $w) => (int) preg_replace('/\D/', '', $w->Wd_No))
            ->values();
        $wardBedStats = $wardsForWidget->map(function (Wd $ward) {
            $beds = Bed::where('Wd_No', $ward->Wd_No);
            $total = (int) $beds->count();
            $available = (int) Bed::where('Wd_No', $ward->Wd_No)->where('BedStatus', 'Available')->count();
            $occupied = (int) Bed::where('Wd_No', $ward->Wd_No)->where('BedStatus', 'Occupied')->count();
            return [
                'ward' => $ward,
                'total' => $total ?: (int) $ward->TotalBeds,
                'available' => $available,
                'occupied' => $occupied,
            ];
        });

        // Recompute total/occupied beds respecting ward filter (Bed availability widget)
        $totalBeds = $wardFilter ? Bed::where('Wd_No', $wardFilter)->count() : Bed::count();
        $occupiedBeds = $wardFilter ? Bed::where('Wd_No', $wardFilter)->where('BedStatus', 'Occupied')->count() : Bed::where('BedStatus', 'Occupied')->count();

        // V1 operational KPIs (all current-data, no history table) — filtered where applicable per spec
        // Waiting for bed: InPatient waiting has no bed yet, so ward filter not directly applicable — keep total but indicate filtered context via bed availability
        $waitingListCount = InPatient::whereNotNull('DateWaitList')->whereNull('DatePlaced')->count();

        $overstayBase = InPatient::whereNotNull('DatePlaced')->whereNull('ActDateLeft');
        if ($wardFilter) {
            $overstayBase->whereHas('bed', fn ($q) => $q->where('Wd_No', $wardFilter));
        }
        $overstayCount = $overstayBase->get()->filter(function ($ip) {
            if (! $ip->DatePlaced || ! $ip->ExpStayDays) return false;
            return $ip->DatePlaced->copy()->addDays((int) $ip->ExpStayDays)->isBefore(Carbon::today());
        })->count();

        $pendingRequisitions = Wardrequisition::where('status', Wardrequisition::STATUS_PENDING)
            ->when($wardFilter, fn ($q) => $q->where('Wd_No', $wardFilter))
            ->count();
        $pendingMedications = MedicationOrder::where('status', MedicationOrder::STATUS_PENDING)->count();

        $nearExpiryCount = Pharmaceutical::whereNotNull('ExpiryDate')->where('ExpiryDate', '<=', Carbon::today()->addDays(30))->where('ExpiryDate', '>=', Carbon::today())->count();
        $expiredCount = Pharmaceutical::whereNotNull('ExpiryDate')->where('ExpiryDate', '<', Carbon::today())->count();

        // 1. Allergy Coverage Widget (Group B - current snapshot, not ward-filtered)
        $totalPatientsAllergyDenom = Patient::count();
        $allergyPatientCount = PatientAllergy::whereNotNull('Pt_No')->distinct()->count('Pt_No');
        $allergyCoverage = $totalPatientsAllergyDenom > 0 ? round($allergyPatientCount / $totalPatientsAllergyDenom * 100, 1) : 0;
        $severeAllergyCount = PatientAllergy::where('Severity', 'Severe')->count();
        $severePatientCount = PatientAllergy::where('Severity', 'Severe')->whereNotNull('Pt_No')->distinct()->count('Pt_No');

        // 2. Staff on Duty breakdown 3 shifts (Morning / Evening / Night) — Group A, ward-filtered
        $staffRotaBase = StfRota::whereDate('WkBegin', $weekStart->toDateString());
        if ($wardFilter) {
            $staffRotaBase->where('Wd_No', $wardFilter);
        }
        $staffShiftBreakdown = (clone $staffRotaBase)->selectRaw('Shift, COUNT(DISTINCT Stf_No) as total')->groupBy('Shift')->pluck('total', 'Shift');
        // Ensure 3 expected shifts always present (validated as Morning,Evening,Night in RotaController)
        foreach (['Morning', 'Evening', 'Night'] as $shift) {
            if (! isset($staffShiftBreakdown[$shift])) {
                $staffShiftBreakdown[$shift] = 0;
            }
        }
        // Recompute distinct total for sanity check
        $staffOnDutyDistinct = $staffRotaBase->distinct('Stf_No')->count('Stf_No');

        // 3. Split reorder into 3 groups (within same card)
        $surgicalAlerts = CentralStock::where('ItemType', CentralStock::TYPE_SURGICAL)->whereColumn('QtyInStock', '<=', 'ReorderLvl')->orderBy('ReorderLvl')->get(['Item_No', 'Name', 'QtyInStock', 'ReorderLvl', 'ItemType']);
        $nonSurgicalAlerts = CentralStock::where('ItemType', CentralStock::TYPE_NON_SURGICAL)->whereColumn('QtyInStock', '<=', 'ReorderLvl')->orderBy('ReorderLvl')->get(['Item_No', 'Name', 'QtyInStock', 'ReorderLvl', 'ItemType']);

        // 5. Last Updated indicator
        $lastUpdated = Carbon::now();

        // Operational today per room — ward-filtered if possible (appointments are per Room, not directly per Ward; keep unfiltered for now)
        $appointmentsTodayByStatusQuery = Appointment::whereDate('ApptDate', $today);
        $appointmentsTodayByStatus = $appointmentsTodayByStatusQuery->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $inConsultationTodayQuery = Appointment::with(['patient', 'room'])->whereDate('ApptDate', $today)->where('status', Appointment::STATUS_IN_CONSULTATION);
        if ($isClinician && $user->stf_no !== null) {
            $inConsultationTodayQuery->where('Consult_Stf_No', $user->stf_no);
        }
        $inConsultationToday = $inConsultationTodayQuery->get();
        $appointmentsTodayCountQuery = Appointment::whereDate('ApptDate', $today);
        $appointmentsToday = $appointmentsTodayCountQuery->count();

        return view('dashboard.index', [
            'totalPatients' => Patient::count(),
            'newPatientsToday' => Patient::whereDate('DateReg', $today)->count(),
            'newPatientsThisWeek' => Patient::whereBetween('DateReg', [$weekStart, $weekEnd])->count(),
            'appointmentsToday' => $appointmentsToday,
            'appointmentsWeek' => $appointmentsWeek,
            'appointmentStatusLabels' => [
                'waiting list' => __('Waiting list'),
                'completed' => __('Completed'),
                'cancelled' => __('Cancelled'),
            ],
            'bedOccupancy' => $totalBeds > 0 ? round($occupiedBeds / $totalBeds * 100, 1) : 0,
            'occupiedBeds' => $occupiedBeds,
            'totalBeds' => $totalBeds,
            'staffOnDuty' => $staffOnDutyDistinct,
            'staffShiftBreakdown' => $staffShiftBreakdown,
            'staffLowThreshold' => self::STAFF_LOW_THRESHOLD,
            'reorderAlerts' => CentralStock::whereColumn('QtyInStock', '<=', 'ReorderLvl')
                ->orderBy('ReorderLvl')
                ->get(['Item_No', 'Name', 'QtyInStock', 'ReorderLvl']),
            'surgicalAlerts' => $surgicalAlerts,
            'nonSurgicalAlerts' => $nonSurgicalAlerts,
            'lowDrugs' => Pharmaceutical::whereColumn('QtyInStock', '<=', 'ReorderLvl')
                ->orderBy('ReorderLvl')
                ->get(['Drug_No', 'Name', 'QtyInStock', 'ReorderLvl']),
            'todayAppointments' => Appointment::with(['patient', 'consultant', 'room'])
                ->whereDate('ApptDate', $today)
                ->when($isClinician && $user->stf_no !== null, fn ($q) => $q->where('Consult_Stf_No', $user->stf_no))
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
                'patientLabel' => __('New patients'),
                'appointmentLabel' => __('Appointments'),
            ],
            'wardBedStats' => $wardBedStats,
            'waitingListCount' => $waitingListCount,
            'overstayCount' => $overstayCount,
            'pendingRequisitions' => $pendingRequisitions,
            'pendingMedications' => $pendingMedications,
            'nearExpiryCount' => $nearExpiryCount,
            'expiredCount' => $expiredCount,
            'appointmentsTodayByStatus' => $appointmentsTodayByStatus,
            'inConsultationToday' => $inConsultationToday,
            'allergyPatientCount' => $allergyPatientCount,
            'allergyCoverage' => $allergyCoverage,
            'severeAllergyCount' => $severeAllergyCount,
            'severePatientCount' => $severePatientCount,
            'wardsForFilter' => $wardsForFilter,
            'wardFilter' => $wardFilter,
            'lastUpdated' => $lastUpdated,
        ]);
    }
}
