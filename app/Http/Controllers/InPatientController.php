<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\InPatient;
use App\Models\Patient;
use App\Models\Wd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InPatientController extends Controller
{
    public function index(Request $request): View
    {
        $query = InPatient::with(['patient', 'bed.ward'])
            ->orderBy('DatePlaced', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('In_Pt_No', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('Pt_No', 'like', "%{$search}%")
                            ->orWhere('FirstName', 'like', "%{$search}%")
                            ->orWhere('LastName', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('ward')) {
            $query->whereHas('bed', function ($bq) use ($request) {
                $bq->where('Wd_No', $request->ward);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'current') {
                $query->whereNull('ActDateLeft');
            } elseif ($request->status === 'discharged') {
                $query->whereNotNull('ActDateLeft');
            } elseif ($request->status === 'waiting') {
                $query->whereNotNull('DateWaitList')->whereNull('DatePlaced');
            }
        }

        if ($request->filled('date_from')) {
            $query->where('DatePlaced', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('DatePlaced', '<=', $request->date_to);
        }

        $inPatients = $query->paginate(15)->withQueryString();

        $wards = Wd::orderBy('Wd_No')->get()->sortBy(fn (Wd $w) => (int) preg_replace('/\D/', '', $w->Wd_No))->values();
        $statuses = ['current', 'discharged', 'waiting'];

        return view('in-patients.index', compact('inPatients', 'wards', 'statuses'));
    }

    public function create(): View
    {
        $patients = Patient::orderBy('LastName')->orderBy('FirstName')->get();
        $beds = Bed::with('ward')->where('BedStatus', 'Available')->orderBy('Bed_No')->get();
        $wards = Wd::orderBy('Wd_No')->get()->sortBy(fn (Wd $w) => (int) preg_replace('/\D/', '', $w->Wd_No))->values();

        return view('in-patients.form', [
            'inPatient' => new InPatient,
            'patients' => $patients,
            'beds' => $beds,
            'wards' => $wards,
            'nextInPtNo' => InPatient::nextNo(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateInPatient($request);
        $data['In_Pt_No'] = InPatient::nextNo();

        $inPatient = InPatient::create($data);

        // Update bed status
        if ($inPatient->Bed_No) {
            Bed::where('Bed_No', $inPatient->Bed_No)->update(['BedStatus' => 'Occupied']);
        }

        return redirect()->route('in-patients.index')
            ->with('status', __('Admitted patient :no.', ['no' => $inPatient->In_Pt_No]));
    }

    public function show(InPatient $inPatient): View
    {
        $inPatient->load(['patient', 'bed.ward']);

        return view('in-patients.show', compact('inPatient'));
    }

    public function edit(InPatient $inPatient): View
    {
        $patients = Patient::orderBy('LastName')->orderBy('FirstName')->get();
        $beds = Bed::with('ward')->where(function ($q) use ($inPatient) {
            $q->where('BedStatus', 'Available')
                ->orWhere('Bed_No', $inPatient->Bed_No);
        })->orderBy('Bed_No')->get();
        $wards = Wd::orderBy('Wd_No')->get()->sortBy(fn (Wd $w) => (int) preg_replace('/\D/', '', $w->Wd_No))->values();

        return view('in-patients.form', compact('inPatient', 'patients', 'beds', 'wards'));
    }

    public function update(Request $request, InPatient $inPatient): RedirectResponse
    {
        $oldBedNo = $inPatient->Bed_No;
        $data = $this->validateInPatient($request);

        $inPatient->update($data);

        // Update bed statuses
        if ($oldBedNo && $oldBedNo !== $inPatient->Bed_No) {
            Bed::where('Bed_No', $oldBedNo)->update(['BedStatus' => 'Available']);
        }
        if ($inPatient->Bed_No) {
            Bed::where('Bed_No', $inPatient->Bed_No)->update(['BedStatus' => 'Occupied']);
        }

        return redirect()->route('in-patients.index')
            ->with('status', __('Updated admission :no.', ['no' => $inPatient->In_Pt_No]));
    }

    public function destroy(InPatient $inPatient): RedirectResponse
    {
        $no = $inPatient->In_Pt_No;
        $bedNo = $inPatient->Bed_No;

        $inPatient->delete();

        if ($bedNo) {
            Bed::where('Bed_No', $bedNo)->update(['BedStatus' => 'Available']);
        }

        return redirect()->route('in-patients.index')
            ->with('status', __('Deleted admission :no.', ['no' => $no]));
    }

    protected function validateInPatient(Request $request): array
    {
        return $request->validate([
            'Pt_No' => ['required', 'exists:Patient,Pt_No'],
            'Bed_No' => ['nullable', 'exists:Bed,Bed_No'],
            'DateWaitList' => ['nullable', 'date'],
            'ExpStayDays' => ['nullable', 'integer', 'min:1'],
            'DatePlaced' => ['nullable', 'date'],
            'DateLeave' => ['nullable', 'date', 'after_or_equal:DatePlaced'],
            'ActDateLeft' => ['nullable', 'date'],
        ]);
    }
}
