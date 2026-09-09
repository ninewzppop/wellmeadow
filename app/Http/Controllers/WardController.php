<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Stf;
use App\Models\Wd;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WardController extends Controller
{
    public function index(): View
    {
        $query = Wd::orderBy('Wd_No');

        // Ward-bound roles (care staff) see their own ward card only;
        // directors and personnel officers see all wards.
        $user = auth()->user();
        $wardBound = $user->hasRole(['charge_nurse', 'doctor', 'consultant', 'senior_nurse', 'staff_nurse', 'auxiliary']);
        if ($wardBound && ! $user->managesAllWards() && $user->wardNo() !== null) {
            $query->where('Wd_No', $user->wardNo());
        }

        $wards = $query->get()->sortBy(fn (Wd $w) => (int) preg_replace('/\D/', '', $w->Wd_No))->values();

        $bedStats = Bed::query()
            ->selectRaw('Wd_No, SUM(BedStatus = "Occupied") AS occupied_beds, SUM(BedStatus = "Available") AS available_beds')
            ->groupBy('Wd_No')
            ->get()
            ->keyBy('Wd_No');

        return view('wards.index', compact('wards', 'bedStats'));
    }

    public function show(Wd $ward): View|RedirectResponse
    {
        $user = auth()->user();
        $wardBound = $user->hasRole(['charge_nurse', 'doctor', 'consultant', 'senior_nurse', 'staff_nurse', 'auxiliary']);
        if ($wardBound && ! $user->managesAllWards() && $ward->Wd_No !== $user->wardNo()) {
            return redirect()->route('forbidden');
        }

        $beds = Bed::where('Wd_No', $ward->Wd_No)->orderBy('Bed_No')->get();

        $available = $beds->where('BedStatus', 'Available')->count();
        $occupied = $beds->where('BedStatus', 'Occupied')->count();
        $staff = Stf::where('Alloc_Wd_No', $ward->Wd_No)
            ->orderBy('LastName')
            ->get(['Stf_No', 'FirstName', 'LastName']);

        return view('wards.show', [
            'ward' => $ward,
            'beds' => $beds,
            'available' => $available,
            'occupied' => $occupied,
            'staff' => $staff,
        ]);
    }
}
