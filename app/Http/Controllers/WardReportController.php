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

        $wards = Wd::with(['rotas' => function ($query) use ($weekBeginning) {
            $query->when($weekBeginning, fn ($q) => $q->whereDate('WkBegin', $weekBeginning))
                ->with(['stf.positions.pos', 'stf.assignedWard']);
        }])
            ->orderBy('Wd_No')
            ->get();

        return view('wards.report', compact('wards', 'weekBeginning'));
    }
}
