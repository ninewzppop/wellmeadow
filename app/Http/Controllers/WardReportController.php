<?php

namespace App\Http\Controllers;

use App\Models\Wd;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WardReportController extends Controller
{
    public function show(Request $request): View
    {
        $weekBeginning = $request->validate(['date' => ['nullable', 'date']])['date'] ?? null;

        // Ward-bound roles see their own ward's rota report only.
        $user = $request->user();
        $wardBound = $user->hasRole(['charge_nurse', 'doctor', 'consultant', 'senior_nurse', 'staff_nurse', 'auxiliary']);

        $wards = Wd::with(['rotas' => function ($query) use ($weekBeginning) {
            $query->when($weekBeginning, fn ($q) => $q->whereDate('WkBegin', $weekBeginning))
                ->with(['stf.positions.pos', 'stf.assignedWard']);
        }])
            ->when(
                $wardBound && ! $user->managesAllWards() && $user->wardNo() !== null,
                fn ($query) => $query->where('Wd_No', $user->wardNo()),
            )
            ->orderBy('Wd_No')
            ->get();

        return view('wards.report', compact('wards', 'weekBeginning'));
    }
}
