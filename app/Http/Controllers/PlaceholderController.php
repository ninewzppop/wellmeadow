<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PlaceholderController extends Controller
{
    /**
     * Menu titles shown on placeholder pages for routes not yet built.
     *
     * @var array<string, string>
     */
    protected const TITLES = [
        'patients' => 'Patients',
        'appointments' => 'Appointments',
        'in-patients' => 'In-patients',
        'medications' => 'Medications',
        'allergies' => 'Allergies',
        'wards' => 'Wards',
        'rooms' => 'Rooms',
        'stock' => 'Stock',
        'pharmacy' => 'Pharmacy',
        'requisitions' => 'Requisitions',
        'rota' => 'Rota',
        'suppliers' => 'Suppliers',
        'local-doctors' => 'Local doctors',
        'users' => 'Users & Roles',
    ];

    public function show(string $page): View
    {
        return view('placeholders.index', [
            'page' => $page,
            'title' => __(self::TITLES[$page] ?? $page),
        ]);
    }
}
