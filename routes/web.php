<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffSearchController;
use App\Http\Controllers\WardReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::resource('staff', StaffController::class)->except(['show']);
    Route::get('staff/search', [StaffSearchController::class, 'search'])->name('staff.search');

    Route::resource('allocations', AllocationController::class)->only(['index', 'store', 'destroy']);

    Route::get('wards/report', [WardReportController::class, 'show'])->name('wards.report');

    // Placeholder routes for pages that will be built later (menu wiring only).
    Route::get('/patients', [PlaceholderController::class, 'show'])->defaults('page', 'patients')->name('patients.index');
    Route::get('/appointments', [PlaceholderController::class, 'show'])->defaults('page', 'appointments')->name('appointments.index');
    Route::get('/in-patients', [PlaceholderController::class, 'show'])->defaults('page', 'in-patients')->name('in-patients.index');
    Route::get('/medications', [PlaceholderController::class, 'show'])->defaults('page', 'medications')->name('medications.index');
    Route::get('/allergies', [PlaceholderController::class, 'show'])->defaults('page', 'allergies')->name('allergies.index');
    Route::get('/wards', [PlaceholderController::class, 'show'])->defaults('page', 'wards')->name('wards.index');
    Route::get('/rooms', [PlaceholderController::class, 'show'])->defaults('page', 'rooms')->name('rooms.index');
    Route::get('/stock', [PlaceholderController::class, 'show'])->defaults('page', 'stock')->name('stock.index');
    Route::get('/pharmacy', [PlaceholderController::class, 'show'])->defaults('page', 'pharmacy')->name('pharmacy.index');
    Route::get('/requisitions', [PlaceholderController::class, 'show'])->defaults('page', 'requisitions')->name('requisitions.index');
    Route::get('/rota', [PlaceholderController::class, 'show'])->defaults('page', 'rota')->name('rota.index');
    Route::get('/suppliers', [PlaceholderController::class, 'show'])->defaults('page', 'suppliers')->name('suppliers.index');
    Route::get('/local-doctors', [PlaceholderController::class, 'show'])->defaults('page', 'local-doctors')->name('local-doctors.index');

    Route::middleware('admin')->group(function () {
        Route::get('/users', [PlaceholderController::class, 'show'])->defaults('page', 'users')->name('users.index');
    });
});
