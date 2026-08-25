<?php

namespace App\Http\Controllers;

use App\Models\LocalDr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocalDoctorController extends Controller
{
    public function index(): View
    {
        $doctors = LocalDr::orderBy('LastName')
            ->orderBy('FirstName')
            ->paginate(15);

        return view('local-doctors.index', compact('doctors'));
    }

    public function create(): View
    {
        return view('local-doctors.form', ['doctor' => new LocalDr]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateDoctor($request);

        $doctor = LocalDr::create($data);

        return redirect()->route('local-doctors.index')
            ->with('status', __('Created local doctor :name.', ['name' => $doctor->full_name]));
    }

    public function show(LocalDr $doctor): View
    {
        return view('local-doctors.show', compact('doctor'));
    }

    public function edit(LocalDr $doctor): View
    {
        return view('local-doctors.form', compact('doctor'));
    }

    public function update(Request $request, LocalDr $doctor): RedirectResponse
    {
        $data = $this->validateDoctor($request);

        $doctor->update($data);

        return redirect()->route('local-doctors.index')
            ->with('status', __('Updated local doctor :name.', ['name' => $doctor->full_name]));
    }

    public function destroy(LocalDr $doctor): RedirectResponse
    {
        $name = $doctor->full_name;

        $doctor->delete();

        return redirect()->route('local-doctors.index')
            ->with('status', __('Deleted local doctor :name.', ['name' => $name]));
    }

    protected function validateDoctor(Request $request): array
    {
        return $request->validate([
            'Clinic_No' => ['required', 'string', 'max:10', 'unique:LocalDr,Clinic_No'],
            'FirstName' => ['nullable', 'string', 'max:50'],
            'LastName' => ['nullable', 'string', 'max:50'],
            'Address' => ['nullable', 'string', 'max:150'],
            'TelNo' => ['nullable', 'string', 'max:15'],
        ]);
    }
}