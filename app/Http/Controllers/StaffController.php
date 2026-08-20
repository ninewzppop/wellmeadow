<?php

namespace App\Http\Controllers;

use App\Models\Pos;
use App\Models\Stf;
use App\Models\StfPos;
use App\Models\StfQual;
use App\Models\StfWorkExp;
use App\Models\Wd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $staff = Stf::with(['positions.pos', 'qualifications', 'workExperiences', 'assignedWard'])
            ->orderBy('LastName')
            ->orderBy('FirstName')
            ->paginate(15);

        return view('staff.index', compact('staff'));
    }

    public function create(): View
    {
        $positions = Pos::orderBy('Pos_Name')->get();
        $wards = Wd::orderBy('Wd_Name')->get();

        return view('staff.form', ['staff' => new Stf, 'positions' => $positions, 'wards' => $wards]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateStaff($request);

        $staff = Stf::create($data);

        $this->saveNested($staff, $request);

        return redirect()->route('staff.index')
            ->with('status', "Created record for {$staff->full_name}.");
    }

    public function edit(Stf $staff): View
    {
        $staff->load(['qualifications', 'workExperiences', 'positions.pos', 'assignedWard']);

        $positions = Pos::orderBy('Pos_Name')->get();
        $wards = Wd::orderBy('Wd_Name')->get();

        return view('staff.form', compact('staff', 'positions', 'wards'));
    }

    public function update(Request $request, Stf $staff): RedirectResponse
    {
        $data = $this->validateStaff($request);

        $staff->update($data);

        $this->saveNested($staff, $request);

        return redirect()->route('staff.index')
            ->with('status', "Updated record for {$staff->full_name}.");
    }

    public function destroy(Stf $staff): RedirectResponse
    {
        $name = $staff->full_name;

        $staff->qualifications()->delete();
        $staff->workExperiences()->delete();
        $staff->positions()->delete();
        $staff->rotas()->delete();
        $staff->delete();

        return redirect()->route('staff.index')
            ->with('status', "Deleted record for {$name}.");
    }

    protected function validateStaff(Request $request): array
    {
        return $request->validate([
            'Stf_No' => ['required', 'string', 'max:10'],
            'FirstName' => ['required', 'string', 'max:50'],
            'LastName' => ['required', 'string', 'max:50'],
            'Address' => ['nullable', 'string', 'max:150'],
            'TelNo' => ['nullable', 'string', 'max:15'],
            'DOB' => ['nullable', 'date'],
            'Sex' => ['nullable', 'string', 'max:1'],
            'NIN' => ['nullable', 'string', 'max:13'],
            'Alloc_Wd_No' => ['nullable', 'exists:Wd,Wd_No'],
        ]);
    }

    protected function saveNested(Stf $staff, Request $request): void
    {
        // Simple approach: rebuild the child rows from the submitted form.
        StfQual::where('Stf_No', $staff->Stf_No)->delete();
        StfWorkExp::where('Stf_No', $staff->Stf_No)->delete();
        StfPos::where('Stf_No', $staff->Stf_No)->delete();

        foreach ($request->input('qualifications', []) as $row) {
            if (empty($row['Type']) && empty($row['Institution'])) {
                continue;
            }

            StfQual::create([
                'Qual_No' => ($row['Qual_No'] ?? '') ?: 'Q'.Str::upper(Str::random(9)),
                'Stf_No' => $staff->Stf_No,
                'Type' => $row['Type'] ?? null,
                'QualDate' => $row['QualDate'] ?? null,
                'Institution' => $row['Institution'] ?? null,
            ]);
        }

        foreach ($request->input('work_experiences', []) as $row) {
            if (empty($row['Organization']) && empty($row['Position'])) {
                continue;
            }

            StfWorkExp::create([
                'WorkExp_No' => ($row['WorkExp_No'] ?? '') ?: 'E'.Str::upper(Str::random(9)),
                'Stf_No' => $staff->Stf_No,
                'Organization' => $row['Organization'] ?? null,
                'Position' => $row['Position'] ?? null,
                'StartDate' => $row['StartDate'] ?? null,
                'FinishDate' => $row['FinishDate'] ?? null,
            ]);
        }

        foreach ($request->input('positions', []) as $row) {
            if (empty($row['Pos_No'])) {
                continue;
            }

            StfPos::create([
                'StfPos_No' => ($row['StfPos_No'] ?? '') ?: 'P'.Str::upper(Str::random(9)),
                'Stf_No' => $staff->Stf_No,
                'Pos_No' => $row['Pos_No'],
                'CurrSalary' => $row['CurrSalary'] ?? null,
                'HrsPerWk' => $row['HrsPerWk'] ?? null,
                'ContractType' => $row['ContractType'] ?? null,
                'PaymentType' => $row['PaymentType'] ?? null,
            ]);
        }
    }
}
