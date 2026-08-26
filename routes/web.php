<?php

use App\Http\Controllers\AllergyController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InPatientController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LocalDoctorController;
use App\Http\Controllers\MedicationOrderController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PharmaceuticalController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RotaController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffSearchController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WardController;
use App\Http\Controllers\WardReportController;
use Illuminate\Support\Facades\Route;

Route::get('language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::resource('staff', StaffController::class)->except(['show']);
    Route::get('staff/search', [StaffSearchController::class, 'search'])->name('staff.search');

    Route::get('wards', [WardController::class, 'index'])->name('wards.index');
    Route::get('wards/report', [WardReportController::class, 'show'])->name('wards.report');
    Route::get('wards/{ward}', [WardController::class, 'show'])->name('wards.show');

    // Placeholder routes for pages that will be built later (menu wiring only).
    Route::resource('patients', PatientController::class);
    Route::resource('appointments', AppointmentController::class);
    Route::resource('in-patients', InPatientController::class);
    Route::get('/medications', [MedicationOrderController::class, 'index'])->name('medications.index');
    Route::get('/medications/{order}', [MedicationOrderController::class, 'show'])->name('medications.show');
    Route::resource('allergies', AllergyController::class)->except(['show']);
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::post('/rooms/{room}/appointments/{appointment}/start', [RoomController::class, 'start'])->name('rooms.start');
    Route::post('/rooms/{room}/appointments/{appointment}/medication-order', [MedicationOrderController::class, 'store'])->name('rooms.medication-order.store');
    Route::post('/medications/{order}/confirm', [MedicationOrderController::class, 'confirm'])->name('medications.confirm');
    Route::post('/medications/{order}/cancel', [MedicationOrderController::class, 'cancel'])->name('medications.cancel');
    Route::post('/rooms/{room}/appointments/{appointment}/admit', [RoomController::class, 'admit'])->name('rooms.admit');
    Route::post('/rooms/{room}/appointments/{appointment}/complete', [RoomController::class, 'complete'])->name('rooms.complete');
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/create', [StockController::class, 'create'])->name('stock.create');
    Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
    Route::get('/stock/{item}/edit', [StockController::class, 'edit'])->name('stock.edit');
    Route::put('/stock/{item}', [StockController::class, 'update'])->name('stock.update');
    Route::delete('/stock/{item}', [StockController::class, 'destroy'])->name('stock.destroy');
    Route::post('/stock/{item}/restock', [StockController::class, 'restock'])->name('stock.restock');
    Route::post('/stock/{item}/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
    Route::get('/stock/{item}/history', [StockController::class, 'history'])->name('stock.history');
    Route::get('/pharmacy', [PharmaceuticalController::class, 'index'])->name('pharmacy.index');
    Route::get('/pharmacy/create', [PharmaceuticalController::class, 'create'])->name('pharmacy.create');
    Route::post('/pharmacy', [PharmaceuticalController::class, 'store'])->name('pharmacy.store');
    Route::get('/pharmacy/{drug}/edit', [PharmaceuticalController::class, 'edit'])->name('pharmacy.edit');
    Route::put('/pharmacy/{drug}', [PharmaceuticalController::class, 'update'])->name('pharmacy.update');
    Route::delete('/pharmacy/{drug}', [PharmaceuticalController::class, 'destroy'])->name('pharmacy.destroy');
    Route::post('/pharmacy/{drug}/restock', [PharmaceuticalController::class, 'restock'])->name('pharmacy.restock');
    Route::post('/pharmacy/{drug}/adjust', [PharmaceuticalController::class, 'adjust'])->name('pharmacy.adjust');
    Route::get('/pharmacy/{drug}/history', [PharmaceuticalController::class, 'history'])->name('pharmacy.history');
    Route::get('/requisitions', [PlaceholderController::class, 'show'])->defaults('page', 'requisitions')->name('requisitions.index');
    Route::get('/rota', [RotaController::class, 'index'])->name('rota.index');
    Route::post('/rota', [RotaController::class, 'store'])->name('rota.store');
    Route::put('/rota/{allocation}', [RotaController::class, 'update'])->name('rota.update');
    Route::delete('/rota/{allocation}', [RotaController::class, 'destroy'])->name('rota.destroy');
    Route::redirect('/allocations', '/rota');
    Route::resource('suppliers', SupplierController::class);
    Route::resource('local-doctors', LocalDoctorController::class)->parameters([
        'local-doctors' => 'doctor',
    ]);

    Route::middleware('admin')->group(function () {
        Route::get('/users', [PlaceholderController::class, 'show'])->defaults('page', 'users')->name('users.index');
    });
});
