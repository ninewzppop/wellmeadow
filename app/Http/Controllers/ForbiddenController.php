<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForbiddenController extends Controller
{
    /**
     * 403 page with a back button suited to the user's role.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $ward = $user?->wardNo();

        $backUrl = match ($user?->role) {
            'personnel_officer' => route('staff.index'),
            'charge_nurse', 'senior_nurse', 'staff_nurse' => $ward
                ? route('wards.show', $ward)
                : route('dashboard.index'),
            'doctor', 'consultant' => route('appointments.index'),
            'auxiliary' => route('rota.index'),
            default => route('dashboard.index'),
        };

        return response()->view('errors.403', ['backUrl' => $backUrl], 403);
    }
}
