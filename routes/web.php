<?php

use App\Http\Controllers\AllocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffSearchController;
use App\Http\Controllers\WardController;
use App\Http\Controllers\WardReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', fn () => redirect()->route('staff.index'));

    Route::resource('staff', StaffController::class)->except(['show']);
    Route::get('staff/search', [StaffSearchController::class, 'search'])->name('staff.search');

    Route::resource('allocations', AllocationController::class)->only(['index', 'store', 'destroy']);

    Route::get('wards', [WardController::class, 'index'])->name('wards.index');
    Route::get('wards/{ward}', [WardController::class, 'show'])->name('wards.show');

    Route::get('wards/report', [WardReportController::class, 'show'])->name('wards.report');
});
