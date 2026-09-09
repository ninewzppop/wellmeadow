<?php

use App\Http\Controllers\AllergyController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForbiddenController;
use App\Http\Controllers\InPatientController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LocalDoctorController;
use App\Http\Controllers\MedicationOrderController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PharmaceuticalController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RotaController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffSearchController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WardController;
use App\Http\Controllers\WardReportController;
use App\Http\Controllers\WardRequisitionController;
use Illuminate\Support\Facades\Route;

Route::get('language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('forbidden', ForbiddenController::class)->name('forbidden');

    Route::get('profile/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('profile/password', [PasswordController::class, 'update'])->name('password.update');

    // Role sets (ADR-0012 route matrix).
    $care = 'role:medical_director,charge_nurse,doctor,consultant,senior_nurse,staff_nurse';
    $prescribe = 'role:medical_director,charge_nurse,doctor,consultant';
    $staffMgr = 'role:medical_director,personnel_officer';
    $wardMgr = 'role:medical_director,charge_nurse';
    $reqView = 'role:medical_director,charge_nurse,senior_nurse,staff_nurse';
    $rotaView = 'role:medical_director,charge_nurse,doctor,consultant,senior_nurse,staff_nurse,auxiliary';
    $director = 'role:medical_director';

    Route::middleware($staffMgr)->group(function () {
        Route::resource('staff', StaffController::class)->except(['show', 'index']);
    });
    Route::middleware('role:medical_director,personnel_officer,doctor,consultant')->group(function () {
        Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('staff/search', [StaffSearchController::class, 'search'])->name('staff.search');
    });

    Route::get('wards', [WardController::class, 'index'])->name('wards.index');
    Route::middleware('role:medical_director,personnel_officer,charge_nurse')->group(function () {
        Route::get('wards/report', [WardReportController::class, 'show'])->name('wards.report');
    });
    Route::get('wards/{ward}', [WardController::class, 'show'])->name('wards.show');

    // Print-friendly reports (context-specific)
    Route::middleware($care)->group(function () {
        Route::get('reports/patients', [ReportController::class, 'patients'])->name('reports.patients');
        Route::get('reports/patients/{patient}', [ReportController::class, 'patient'])->name('reports.patient');
        Route::get('reports/rooms/{room}', [ReportController::class, 'consult'])->name('reports.consult');
        Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('reports/pharmacy', [ReportController::class, 'pharmacy'])->name('reports.pharmacy');
    });
    Route::get('reports/wards', [ReportController::class, 'wards'])->name('reports.wards');
    Route::get('reports/wards/{ward}', [ReportController::class, 'ward'])->name('reports.ward');

    // Placeholder routes for pages that will be built later (menu wiring only).
    // NOTE: write routes (except) must register BEFORE read routes (only):
    // otherwise "show" captures "/create" and returns 404.
    Route::middleware($prescribe)->group(function () {
        Route::resource('patients', PatientController::class)->except(['index', 'show']);
        Route::resource('appointments', AppointmentController::class)->except(['index', 'show']);
        Route::resource('in-patients', InPatientController::class)->except(['index', 'show']);
        Route::resource('allergies', AllergyController::class)->except(['index', 'show']);
        Route::post('/rooms/{room}/appointments/{appointment}/start', [RoomController::class, 'start'])->name('rooms.start');
        Route::post('/rooms/{room}/appointments/{appointment}/medication-order', [MedicationOrderController::class, 'store'])->name('rooms.medication-order.store');
        Route::post('/rooms/{room}/appointments/{appointment}/admit', [RoomController::class, 'admit'])->name('rooms.admit');
        Route::post('/rooms/{room}/appointments/{appointment}/complete', [RoomController::class, 'complete'])->name('rooms.complete');
    });
    Route::middleware($care)->group(function () {
        Route::resource('patients', PatientController::class)->only(['index', 'show']);
        Route::resource('appointments', AppointmentController::class)->only(['index', 'show']);
        Route::resource('in-patients', InPatientController::class)->only(['index', 'show']);
        Route::resource('allergies', AllergyController::class)->only(['index']);
        Route::get('/medications', [MedicationOrderController::class, 'index'])->name('medications.index');
        Route::get('/medications/{order}', [MedicationOrderController::class, 'show'])->name('medications.show');
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    });
    Route::middleware($wardMgr)->group(function () {
        Route::post('/medications/{order}/confirm', [MedicationOrderController::class, 'confirm'])->name('medications.confirm');
        Route::post('/medications/{order}/cancel', [MedicationOrderController::class, 'cancel'])->name('medications.cancel');
    });

    Route::middleware($care)->group(function () {
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/stock/{item}/history', [StockController::class, 'history'])->name('stock.history');
        Route::get('/pharmacy', [PharmaceuticalController::class, 'index'])->name('pharmacy.index');
        Route::get('/pharmacy/{drug}/history', [PharmaceuticalController::class, 'history'])->name('pharmacy.history');
    });
    Route::middleware($director)->group(function () {
        Route::get('/stock/create', [StockController::class, 'create'])->name('stock.create');
        Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
        Route::get('/stock/{item}/edit', [StockController::class, 'edit'])->name('stock.edit');
        Route::put('/stock/{item}', [StockController::class, 'update'])->name('stock.update');
        Route::delete('/stock/{item}', [StockController::class, 'destroy'])->name('stock.destroy');
        Route::post('/stock/{item}/restock', [StockController::class, 'restock'])->name('stock.restock');
        Route::post('/stock/{item}/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
        Route::get('/pharmacy/create', [PharmaceuticalController::class, 'create'])->name('pharmacy.create');
        Route::post('/pharmacy', [PharmaceuticalController::class, 'store'])->name('pharmacy.store');
        Route::get('/pharmacy/{drug}/edit', [PharmaceuticalController::class, 'edit'])->name('pharmacy.edit');
        Route::put('/pharmacy/{drug}', [PharmaceuticalController::class, 'update'])->name('pharmacy.update');
        Route::delete('/pharmacy/{drug}', [PharmaceuticalController::class, 'destroy'])->name('pharmacy.destroy');
        Route::post('/pharmacy/{drug}/restock', [PharmaceuticalController::class, 'restock'])->name('pharmacy.restock');
        Route::post('/pharmacy/{drug}/adjust', [PharmaceuticalController::class, 'adjust'])->name('pharmacy.adjust');
    });
    Route::middleware($reqView)->group(function () {
        Route::get('/requisitions', [WardRequisitionController::class, 'index'])->name('requisitions.index');
        Route::get('/requisitions/history', [WardRequisitionController::class, 'history'])->name('requisitions.history');
        Route::get('/requisitions/report', [WardRequisitionController::class, 'report'])->name('requisitions.report');
        Route::get('/requisitions/{requisition}', [WardRequisitionController::class, 'show'])->name('requisitions.show');
    });
    Route::middleware($wardMgr)->group(function () {
        Route::get('/requisitions/create', [WardRequisitionController::class, 'create'])->name('requisitions.create');
        Route::post('/requisitions', [WardRequisitionController::class, 'store'])->name('requisitions.store');
        Route::get('/requisitions/{requisition}/edit', [WardRequisitionController::class, 'edit'])->name('requisitions.edit');
        Route::put('/requisitions/{requisition}', [WardRequisitionController::class, 'update'])->name('requisitions.update');
        Route::delete('/requisitions/{requisition}', [WardRequisitionController::class, 'destroy'])->name('requisitions.destroy');
        Route::post('/requisitions/{requisition}/approve', [WardRequisitionController::class, 'approve'])->name('requisitions.approve');
        Route::post('/requisitions/{requisition}/receive', [WardRequisitionController::class, 'receive'])->name('requisitions.receive');
    });
    Route::middleware($rotaView)->group(function () {
        Route::get('/rota', [RotaController::class, 'index'])->name('rota.index');
    });
    Route::middleware($wardMgr)->group(function () {
        Route::post('/rota', [RotaController::class, 'store'])->name('rota.store');
        Route::put('/rota/{allocation}', [RotaController::class, 'update'])->name('rota.update');
        Route::delete('/rota/{allocation}', [RotaController::class, 'destroy'])->name('rota.destroy');
    });
    Route::redirect('/allocations', '/rota');
    Route::middleware($director)->group(function () {
        Route::resource('suppliers', SupplierController::class)->except(['index', 'show']);
        Route::resource('local-doctors', LocalDoctorController::class)->except(['index', 'show'])->parameters([
            'local-doctors' => 'doctor',
        ]);
    });
    Route::middleware($care)->group(function () {
        Route::resource('suppliers', SupplierController::class)->only(['index', 'show']);
        Route::resource('local-doctors', LocalDoctorController::class)->only(['index', 'show'])->parameters([
            'local-doctors' => 'doctor',
        ]);
    });

    Route::middleware('admin')->group(function () {
        Route::get('/users', [PlaceholderController::class, 'show'])->defaults('page', 'users')->name('users.index');
    });
});
