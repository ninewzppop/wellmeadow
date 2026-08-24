<?php

use App\Http\Controllers\AllergyController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InPatientController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\RotaController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffSearchController;
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
    Route::get('/medications', [PlaceholderController::class, 'show'])->defaults('page', 'medications')->name('medications.index');
    Route::resource('allergies', AllergyController::class)->except(['show']);
    Route::get('/rooms', [PlaceholderController::class, 'show'])->defaults('page', 'rooms')->name('rooms.index');
    Route::get('/stock', [PlaceholderController::class, 'show'])->defaults('page', 'stock')->name('stock.index');
    Route::get('/pharmacy', [PlaceholderController::class, 'show'])->defaults('page', 'pharmacy')->name('pharmacy.index');
    Route::get('/requisitions', [PlaceholderController::class, 'show'])->defaults('page', 'requisitions')->name('requisitions.index');
    Route::get('/rota', [RotaController::class, 'index'])->name('rota.index');
    Route::post('/rota', [RotaController::class, 'store'])->name('rota.store');
    Route::put('/rota/{allocation}', [RotaController::class, 'update'])->name('rota.update');
    Route::delete('/rota/{allocation}', [RotaController::class, 'destroy'])->name('rota.destroy');
    Route::redirect('/allocations', '/rota');
    Route::get('/suppliers', [PlaceholderController::class, 'show'])->defaults('page', 'suppliers')->name('suppliers.index');
    Route::get('/local-doctors', [PlaceholderController::class, 'show'])->defaults('page', 'local-doctors')->name('local-doctors.index');

    Route::middleware('admin')->group(function () {
        Route::get('/users', [PlaceholderController::class, 'show'])->defaults('page', 'users')->name('users.index');
    });
});
