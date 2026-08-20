<?php

namespace App\Http\Controllers;

use App\Models\Stf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffSearchController extends Controller
{
    public function index(): View
    {
        return view('staff.search');
    }

    public function search(Request $request): View
    {
        $data = $request->validate([
            'qualification' => ['nullable', 'string', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'experience_position' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Stf::with(['qualifications', 'workExperiences', 'positions.pos', 'assignedWard']);

        if (! empty($data['qualification'])) {
            $query->whereHas('qualifications', fn ($q) => $q->where('Type', 'like', '%'.$data['qualification'].'%'));
        }

        if (! empty($data['institution'])) {
            $query->whereHas('qualifications', fn ($q) => $q->where('Institution', 'like', '%'.$data['institution'].'%'));
        }

        if (! empty($data['organization'])) {
            $query->whereHas('workExperiences', fn ($q) => $q->where('Organization', 'like', '%'.$data['organization'].'%'));
        }

        if (! empty($data['experience_position'])) {
            $query->whereHas('workExperiences', fn ($q) => $q->where('Position', 'like', '%'.$data['experience_position'].'%'));
        }

        $staff = $query->orderBy('LastName')->orderBy('FirstName')->get();

        return view('staff.search', [
            'staff' => $staff,
            'criteria' => array_filter($data),
        ]);
    }
}
