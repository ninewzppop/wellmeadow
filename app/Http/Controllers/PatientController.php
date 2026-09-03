<?php

namespace App\Http\Controllers;

use App\Models\LocalDr;
use App\Models\Patient;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Patient::with(['localDoctor', 'appointments', 'inPatients.bed', 'nextOfKins', 'allergies']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Pt_No', 'like', "%{$search}%")
                    ->orWhere('FirstName', 'like', "%{$search}%")
                    ->orWhere('LastName', 'like', "%{$search}%")
                    ->orWhere('TelNo', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('clinic_no')) {
            $query->where('Clinic_No', $request->clinic_no);
        }

        // Sorting: allow name, pt_no, datereg, tel
        $allowedSorts = ['name', 'pt_no', 'datereg', 'tel'];
        $allowedDirections = ['asc', 'desc'];
        $sort = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : null;
        $direction = in_array(strtolower((string) $request->query('direction')), $allowedDirections, true) ? strtolower((string) $request->query('direction')) : 'asc';

        // Default sort: newest first by Pt_No numeric desc (clear default)
        if ($sort === null) {
            // Default: newest first — sort by Pt_No numeric descending (LENGTH + lexical is numeric for PT prefix)
            $query->orderByRaw('LENGTH(Pt_No) DESC')->orderBy('Pt_No', 'desc');
            $sort = 'pt_no';
            $direction = 'desc';
        } else {
            match ($sort) {
                'name' => $query->orderBy('LastName', $direction)->orderBy('FirstName', $direction),
                'pt_no' => $query->orderByRaw('LENGTH(Pt_No) '.$direction)->orderBy('Pt_No', $direction),
                'datereg' => $query->orderBy('DateReg', $direction)->orderByRaw('LENGTH(Pt_No) DESC'),
                'tel' => $query->orderBy('TelNo', $direction),
                default => null,
            };
        }

        $patients = $query->paginate(15)->withQueryString();

        $doctors = LocalDr::orderBy('LastName')->orderBy('FirstName')->get();

        return view('patients.index', compact('patients', 'doctors', 'sort', 'direction'));
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

        $patient = $this->createPatientAtomically($data);

        return redirect()->route('patients.index')
            ->with('status', __('Created patient :name with ID :id.', ['name' => $patient->full_name, 'id' => $patient->Pt_No]));
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
            'medications.drug',
            'medications.staff',
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
        $data = $this->validatePatient($request);

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

    protected function validatePatient(Request $request): array
    {
        return $request->validate([
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

    protected function nextPtNo(): string
    {
        $max = Patient::where('Pt_No', 'like', 'PT%')
            ->pluck('Pt_No')
            ->map(fn (string $ptNo) => (int) substr($ptNo, 2))
            ->max();

        return 'PT'.(($max ?? 0) + 1);
    }

    protected function createPatientAtomically(array $data): Patient
    {
        $attempts = 0;
        while (true) {
            try {
                return DB::transaction(function () use ($data) {
                    // Lock the patient table rows for the max calculation to prevent race.
                    // Using lockForUpdate ensures other concurrent transactions wait.
                    $max = DB::table('Patient')
                        ->lockForUpdate()
                        ->where('Pt_No', 'like', 'PT%')
                        ->pluck('Pt_No')
                        ->map(fn (string $ptNo) => (int) substr($ptNo, 2))
                        ->max();

                    $nextNo = 'PT'.(($max ?? 0) + 1);

                    // Defensive: ensure not already taken (covers edge where lock not effective e.g. SQLite)
                    while (DB::table('Patient')->where('Pt_No', $nextNo)->exists()) {
                        $nextNo = 'PT'.(((int) substr($nextNo, 2)) + 1);
                    }

                    $data['Pt_No'] = $nextNo;

                    return Patient::create($data);
                }, 3);
            } catch (QueryException $e) {
                // 23000 = integrity constraint violation (duplicate Pt_No) — retry with new number
                $code = $e->getCode();
                $sqlState = $e->errorInfo[0] ?? null;
                if (($sqlState === '23000' || $code === '23000' || str_contains($e->getMessage(), 'Duplicate')) && $attempts < 5) {
                    $attempts++;
                    continue;
                }
                throw $e;
            }
        }
    }
}
