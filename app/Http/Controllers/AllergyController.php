<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\Pharmaceutical;
use App\Models\Stf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AllergyController extends Controller
{
    public const SEVERITIES = ['Mild', 'Moderate', 'Severe'];

    public function index(Request $request): View
    {
        $query = PatientAllergy::with(['patient', 'drug', 'recordedBy'])
            ->orderBy('DiagDate', 'desc')
            ->orderBy('Allergy_No');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Allergy_No', 'like', "%{$search}%")
                    ->orWhere('Allergy_Name', 'like', "%{$search}%")
                    ->orWhere('Reaction', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('Pt_No', 'like', "%{$search}%")
                            ->orWhere('FirstName', 'like', "%{$search}%")
                            ->orWhere('LastName', 'like', "%{$search}%");
                    })
                    ->orWhereHas('drug', function ($dq) use ($search) {
                        $dq->where('Name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('severity')) {
            $query->where('Severity', $request->severity);
        }

        if ($request->filled('patient')) {
            $query->where('Pt_No', $request->patient);
        }

        $allergies = $query->paginate(15)->withQueryString();

        $patients = Patient::orderBy('LastName')->orderBy('FirstName')->get();

        return view('allergies.index', [
            'allergies' => $allergies,
            'patients' => $patients,
            'severities' => self::SEVERITIES,
        ]);
    }

    public function create(): View
    {
        return view('allergies.form', [
            'allergy' => new PatientAllergy,
            'patients' => Patient::orderBy('LastName')->orderBy('FirstName')->get(),
            'drugs' => Pharmaceutical::orderBy('Name')->get(),
            'staff' => Stf::orderBy('LastName')->orderBy('FirstName')->get(),
            'severities' => self::SEVERITIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAllergy($request);

        PatientAllergy::create($data);

        return redirect()->route('allergies.index')
            ->with('status', __('Created allergy record :no.', ['no' => $data['Allergy_No']]));
    }

    public function edit(PatientAllergy $allergy): View
    {
        return view('allergies.form', [
            'allergy' => $allergy,
            'patients' => Patient::orderBy('LastName')->orderBy('FirstName')->get(),
            'drugs' => Pharmaceutical::orderBy('Name')->get(),
            'staff' => Stf::orderBy('LastName')->orderBy('FirstName')->get(),
            'severities' => self::SEVERITIES,
        ]);
    }

    public function update(Request $request, PatientAllergy $allergy): RedirectResponse
    {
        $data = $this->validateAllergy($request, $allergy->Allergy_No);

        $allergy->update($data);

        return redirect()->route('allergies.index')
            ->with('status', __('Updated allergy record :no.', ['no' => $allergy->Allergy_No]));
    }

    public function destroy(PatientAllergy $allergy): RedirectResponse
    {
        $no = $allergy->Allergy_No;

        $allergy->delete();

        return redirect()->route('allergies.index')
            ->with('status', __('Deleted allergy record :no.', ['no' => $no]));
    }

    protected function validateAllergy(Request $request, ?string $ignoreAllergyNo = null): array
    {
        return $request->validate([
            'Allergy_No' => [
                'required',
                'string',
                'max:10',
                $ignoreAllergyNo ? 'unique:PatientAllergy,Allergy_No,'.$ignoreAllergyNo.',Allergy_No' : 'unique:PatientAllergy,Allergy_No',
            ],
            'Pt_No' => ['nullable', 'exists:Patient,Pt_No'],
            'Drug_No' => ['nullable', 'exists:Pharmaceutical,Drug_No'],
            'Allergy_Name' => ['nullable', 'string', 'max:100'],
            'Reaction' => ['required', 'string', 'max:150'],
            'Severity' => ['required', 'string', 'max:15', 'in:'.implode(',', self::SEVERITIES)],
            'DiagDate' => ['required', 'date'],
            'Rec_Stf_No' => ['nullable', 'exists:Stf,Stf_No'],
        ]);
    }
}
