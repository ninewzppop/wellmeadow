<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Wd;
use Illuminate\View\View;

class WardController extends Controller
{
    public function index(): View
    {
        $wards = Wd::orderBy('Wd_No')->get();

        $bedStats = Bed::query()
            ->selectRaw('Wd_No, SUM(BedStatus = "Occupied") AS occupied_beds, SUM(BedStatus = "Available") AS available_beds')
            ->groupBy('Wd_No')
            ->get()
            ->keyBy('Wd_No');

        return view('wards.index', compact('wards', 'bedStats'));
    }

    public function show(Wd $ward): View
    {
        $beds = Bed::where('Wd_No', $ward->Wd_No)->orderBy('Bed_No')->get();

        $available = $beds->where('BedStatus', 'Available')->count();
        $occupied = $beds->where('BedStatus', 'Occupied')->count();

        return view('wards.show', [
            'ward'      => $ward,
            'beds'      => $beds,
            'available' => $available,
            'occupied'  => $occupied,
            //'staff'     => $staffQuery, // ต้อง query เพิ่ม
            ]);
    }
}