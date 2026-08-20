<?php

namespace App\Http\Controllers;

use App\Models\Stf;
use App\Models\StfRota;
use App\Models\Wd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AllocationController extends Controller
{
    public function index(): View
    {
        $allocations = StfRota::with(['stf', 'wd'])
            ->orderByDesc('WkBegin')
            ->limit(100)
            ->get();

        $staff = Stf::orderBy('LastName')->orderBy('FirstName')->get();
        $wards = Wd::orderBy('Wd_Name')->get();

        return view('allocations.index', compact('allocations', 'staff', 'wards'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'Stf_No' => ['required', 'exists:Stf,Stf_No'],
            'Wd_No' => ['required', 'exists:Wd,Wd_No'],
            'WkBegin' => ['required', 'date'],
            'Shift' => ['required', 'in:Morning,Evening,Night'],
        ]);

        StfRota::create([
            'StfRota_No' => 'R'.Str::upper(Str::random(9)),
            ...$data,
        ]);

        return redirect()->route('allocations.index')
            ->with('status', 'Allocation recorded.');
    }

    public function destroy(StfRota $allocation): RedirectResponse
    {
        $allocation->delete();

        return redirect()->route('allocations.index')
            ->with('status', 'Allocation removed.');
    }
}
