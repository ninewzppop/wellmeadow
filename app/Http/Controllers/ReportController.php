<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Appointment;
use App\Models\CentralStock;
use App\Models\Patient;
use App\Models\Pharmaceutical;
use App\Models\Room;
use App\Models\Wd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function patients(Request $request): View
    {
        $query = Patient::with(['localDoctor', 'allergies'])
            ->orderByRaw('LENGTH(Pt_No) DESC')->orderBy('Pt_No', 'desc');

        $ids = $request->user()->accessiblePatientIds();
        if ($ids !== null) {
            $query->whereIn('Pt_No', $ids);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Pt_No', 'like', "%{$search}%")
                    ->orWhere('FirstName', 'like', "%{$search}%")
                    ->orWhere('LastName', 'like', "%{$search}%")
                    ->orWhere('TelNo', 'like', "%{$search}%");
            });
        }
        if ($request->filled('clinic_no')) {
            $query->where('Clinic_No', $request->clinic_no);
        }

        $patients = $query->limit(500)->get();

        return view('reports.patients', compact('patients'));
    }

    public function patient(Patient $patient): View|RedirectResponse
    {
        if (Gate::denies('view', $patient)) {
            return redirect()->route('forbidden');
        }

        $patient->load([
            'localDoctor',
            'appointments.consultant',
            'appointments.room',
            'inPatients.bed.ward',
            'nextOfKins',
            'allergies.drug',
            'allergies.recordedBy',
            'medications.drug',
            'medications.staff',
        ]);

        return view('reports.patient', compact('patient'));
    }

    public function consult(Request $request, Room $room): View
    {
        $date = $request->query('date');
        if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strtotime($date)) {
            $reportDate = \Carbon\Carbon::parse($date)->startOfDay();
        } else {
            $reportDate = \Carbon\Carbon::today();
        }

        $doctorStfNo = $request->user()->isClinician() ? $request->user()->stf_no : null;

        $queue = Appointment::with(['patient.allergies.drug', 'patient.medications.drug', 'consultant'])
            ->where('Room_No', $room->Room_No)
            ->whereDate('ApptDate', $reportDate->toDateString())
            ->when($doctorStfNo !== null, fn ($q) => $q->where('Consult_Stf_No', $doctorStfNo))
            ->orderBy('ApptTime')->orderBy('Appt_No')
            ->get();

        $finished = Appointment::with(['patient', 'consultant'])
            ->where('Room_No', $room->Room_No)
            ->whereDate('ApptDate', $reportDate->toDateString())
            ->whereIn('status', Appointment::COMPLETED_STATUSES)
            ->when($doctorStfNo !== null, fn ($q) => $q->where('Consult_Stf_No', $doctorStfNo))
            ->orderByDesc('ApptTime')
            ->get();

        return view('reports.consult', compact('room', 'reportDate', 'queue', 'finished'));
    }

    public function stock(Request $request): View
    {
        $query = CentralStock::with('supplier')->orderBy('Item_No');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('Item_No', 'like', "%{$s}%")
                    ->orWhere('Name', 'like', "%{$s}%")
                    ->orWhere('Description', 'like', "%{$s}%");
            });
        }
        if ($request->filled('type') && in_array($request->type, [CentralStock::TYPE_SURGICAL, CentralStock::TYPE_NON_SURGICAL], true)) {
            $query->where('ItemType', $request->type);
        }

        $items = $query->limit(1000)->get();
        $summary = [
            'total' => $items->count(),
            'low' => $items->filter(fn ($i) => ($i->QtyInStock ?? 0) > 0 && ($i->QtyInStock ?? 0) <= ($i->ReorderLvl ?? 0))->count(),
            'out' => $items->filter(fn ($i) => ($i->QtyInStock ?? 0) === 0)->count(),
            'totalValue' => $items->sum(fn ($i) => ($i->QtyInStock ?? 0) * ($i->CostPerUnit ?? 0)),
        ];

        return view('reports.stock', compact('items', 'summary'));
    }

    public function pharmacy(Request $request): View
    {
        $query = Pharmaceutical::with('supplier')->orderBy('Drug_No');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('Drug_No', 'like', "%{$s}%")
                    ->orWhere('Name', 'like', "%{$s}%")
                    ->orWhere('Description', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status') && in_array($request->status, [Pharmaceutical::STOCK_OUT, Pharmaceutical::STOCK_LOW, Pharmaceutical::STOCK_NORMAL], true)) {
            $query->stockStatus($request->status);
        }
        if ($request->filled('expiry') && in_array($request->expiry, [Pharmaceutical::EXPIRY_EXPIRED, Pharmaceutical::EXPIRY_NEAR, Pharmaceutical::EXPIRY_OK], true)) {
            $query->expiryStatus($request->expiry);
        }

        $items = $query->limit(1000)->get();
        $summary = [
            'total' => $items->count(),
            'low' => $items->filter(fn ($i) => $i->stock_status === Pharmaceutical::STOCK_LOW)->count(),
            'out' => $items->filter(fn ($i) => $i->stock_status === Pharmaceutical::STOCK_OUT)->count(),
            'expired' => $items->filter(fn ($i) => $i->expiry_status === Pharmaceutical::EXPIRY_EXPIRED)->count(),
            'nearExpiry' => $items->filter(fn ($i) => $i->expiry_status === Pharmaceutical::EXPIRY_NEAR)->count(),
            'totalValue' => $items->sum(fn ($i) => ($i->QtyInStock ?? 0) * ($i->CostPerUnit ?? 0)),
        ];

        return view('reports.pharmacy', compact('items', 'summary'));
    }

    public function ward(Wd $ward): View|RedirectResponse
    {
        if (! $this->wardReportInScope($ward->Wd_No)) {
            return redirect()->route('forbidden');
        }

        $beds = Bed::where('Wd_No', $ward->Wd_No)->orderBy('Bed_No')->get();
        $available = $beds->where('BedStatus', 'Available')->count();
        $occupied = $beds->where('BedStatus', 'Occupied')->count();

        // Also include all wards summary for context when viewing single ward report
        $allWards = Wd::orderBy('Wd_No')->get();
        $bedStats = Bed::query()
            ->selectRaw('Wd_No, SUM(BedStatus = "Occupied") AS occupied_beds, SUM(BedStatus = "Available") AS available_beds')
            ->groupBy('Wd_No')
            ->get()
            ->keyBy('Wd_No');

        return view('reports.ward', compact('ward', 'beds', 'available', 'occupied', 'allWards', 'bedStats'));
    }

    public function wards(): View
    {
        $query = Wd::orderBy('Wd_No');

        $user = auth()->user();
        if ($this->wardBound($user) && ! $user->managesAllWards() && $user->wardNo() !== null) {
            $query->where('Wd_No', $user->wardNo());
        }

        $wards = $query->get();
        $bedStats = Bed::query()
            ->selectRaw('Wd_No, SUM(BedStatus = "Occupied") AS occupied_beds, SUM(BedStatus = "Available") AS available_beds')
            ->groupBy('Wd_No')
            ->get()
            ->keyBy('Wd_No');

        return view('reports.wards', compact('wards', 'bedStats'));
    }

    /**
     * Roles tied to a ward see only their own ward in ward reports.
     */
    private function wardBound($user): bool
    {
        return $user->hasRole(['charge_nurse', 'doctor', 'consultant', 'senior_nurse', 'staff_nurse', 'auxiliary']);
    }

    private function wardReportInScope(string $wardNo): bool
    {
        $user = auth()->user();

        return ! $this->wardBound($user)
            || $user->managesAllWards()
            || $wardNo === $user->wardNo();
    }
}
