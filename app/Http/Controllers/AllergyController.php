<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\Pharmaceutical;
use App\Models\Stf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AllergyController extends Controller
{
    public const SEVERITIES = ['Mild', 'Moderate', 'Severe'];

    private const GROUPS_PER_PAGE = 10;

    public function index(Request $request): View
    {
        $viewMode = $request->query('view', 'grouped');
        if (! in_array($viewMode, ['grouped', 'flat'], true)) {
            $viewMode = 'grouped';
        }

        $data = array_merge([
            'patients' => Patient::orderBy('LastName')->orderBy('FirstName')->get(),
            'allergyTotals' => $this->allergyTotalsPerPatient(),
            'severities' => self::SEVERITIES,
            'viewMode' => $viewMode,
        ], $viewMode === 'flat' ? $this->flatListing($request) : $this->groupedListing($request));

        return view('allergies.index', $data);
    }

    private function flatListing(Request $request): array
    {
        return [
            'allergies' => $this->filteredAllergies($request)
                ->orderBy('DiagDate', 'desc')
                ->orderBy('Allergy_No')
                ->paginate(15)
                ->withQueryString(),
            'patientGroups' => null,
        ];
    }

    private function groupedListing(Request $request): array
    {
        $records = $this->filteredAllergies($request)
            ->orderBy('DiagDate', 'desc')
            ->orderBy('Allergy_No')
            ->get();

        $groups = $records
            ->groupBy(fn (PatientAllergy $allergy) => $allergy->Pt_No ?? '')
            ->map(fn (Collection $rows) => [
                'patient' => $rows->first()->patient,
                'allergies' => $rows->values(),
                'count' => $rows->count(),
                'hasSevere' => $rows->contains(fn (PatientAllergy $row) => $row->Severity === 'Severe'),
            ])
            ->values()
            ->sort(function (array $a, array $b) {
                if ((! $a['patient']) xor (! $b['patient'])) {
                    return $a['patient'] ? -1 : 1;
                }

                if ($a['patient'] && $b['patient']) {
                    return [$a['patient']->LastName, $a['patient']->FirstName]
                        <=> [$b['patient']->LastName, $b['patient']->FirstName];
                }

                return 0;
            })
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();

        $patientGroups = new LengthAwarePaginator(
            $groups->slice(($page - 1) * self::GROUPS_PER_PAGE, self::GROUPS_PER_PAGE)->values(),
            $groups->count(),
            self::GROUPS_PER_PAGE,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()],
        );
        $patientGroups->withQueryString();

        return [
            'allergies' => null,
            'patientGroups' => $patientGroups,
        ];
    }

    private function allergyTotalsPerPatient(): Collection
    {
        return PatientAllergy::query()
            ->whereNotNull('Pt_No')
            ->selectRaw('Pt_No, COUNT(*) as total')
            ->groupBy('Pt_No')
            ->pluck('total', 'Pt_No');
    }

    private function filteredAllergies(Request $request): Builder
    {
        $query = PatientAllergy::with(['patient', 'drug', 'recordedBy']);

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

        return $query;
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
        $data['Allergy_No'] = $this->generateId('PatientAllergy', 'Allergy_No', 'AL');

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
        $data = $this->validateAllergy($request);

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

    protected function validateAllergy(Request $request): array
    {
        return $request->validate([
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
