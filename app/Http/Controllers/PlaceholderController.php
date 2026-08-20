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
        'patients' => 'ผู้ป่วย',
        'appointments' => 'นัดหมาย',
        'in-patients' => 'ผู้ป่วยใน',
        'medications' => 'ใบสั่งยา',
        'allergies' => 'อาการแพ้',
        'wards' => 'วอร์ด',
        'rooms' => 'ห้องตรวจ',
        'stock' => 'สต็อกกลาง',
        'pharmacy' => 'เภสัชภัณฑ์',
        'requisitions' => 'ใบเบิกของ',
        'rota' => 'ตารางเวร',
        'suppliers' => 'ซัพพลายเออร์',
        'local-doctors' => 'แพทย์ท้องถิ่น',
        'users' => 'ผู้ใช้ & บทบาท',
    ];

    public function show(string $page): View
    {
        return view('placeholders.index', [
            'page' => $page,
            'title' => self::TITLES[$page] ?? $page,
        ]);
    }
}
