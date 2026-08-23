<?php

namespace App\Http\Controllers;

use App\Models\LocalDr;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Patient::with(['localDoctor', 'appointments', 'inPatients.bed', 'nextOfKins', 'allergies'])
            ->orderBy('LastName')
            ->orderBy('FirstName');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Pt_No', 'like', "%{$search}%")
                    ->orWhere('FirstName', 'like', "%{$search}%")
                    ->orWhere('LastName', 'like', "%{$search}%")
                    ->orWhere('TelNo', 'like', "%{$search}%");
            });
        }

        $patients = $query->paginate(15)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create(): View
    {
        $doctors = LocalDr::orderBy('LastName')->orderBy('FirstName')->get();

        return view('patients.form', [
            'patient' => new Patient,
            'doctors' => $doctors,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePatient($request);

        $patient = Patient::create($data);

        return redirect()->route('patients.index')
            ->with('status', __('Created patient :name.', ['name' => $patient->full_name]));
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'localDoctor',
            'appointments.consultant',
            'appointments.room',
            'inPatients.bed.ward',
            'nextOfKins',
            'allergies.drug',
            'allergies.recordedBy',
        ]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        $doctors = LocalDr::orderBy('LastName')->orderBy('FirstName')->get();

        return view('patients.form', compact('patient', 'doctors'));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $data = $this->validatePatient($request, $patient->Pt_No);

        $patient->update($data);

        return redirect()->route('patients.index')
            ->with('status', __('Updated patient :name.', ['name' => $patient->full_name]));
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $name = $patient->full_name;

        $patient->delete();

        return redirect()->route('patients.index')
            ->with('status', __('Deleted patient :name.', ['name' => $name]));
    }

    protected function validatePatient(Request $request, ?string $ignorePtNo = null): array
    {
        return $request->validate([
            'Pt_No' => [
                'required',
                'string',
                'max:10',
                $ignorePtNo ? 'unique:Patient,Pt_No,' . $ignorePtNo . ',Pt_No' : 'unique:Patient,Pt_No',
            ],
            'FirstName' => ['required', 'string', 'max:50'],
            'LastName' => ['required', 'string', 'max:50'],
            'Address' => ['nullable', 'string', 'max:150'],
            'TelNo' => ['nullable', 'string', 'max:15'],
            'DOB' => ['nullable', 'date'],
            'Sex' => ['nullable', 'string', 'max:1'],
            'MaritalStat' => ['nullable', 'string', 'max:15'],
            'DateReg' => ['nullable', 'date'],
            'Clinic_No' => ['nullable', 'exists:LocalDr,Clinic_No'],
        ]);
    }
}