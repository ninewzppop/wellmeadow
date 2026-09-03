<?php

namespace App\Providers;

use App\Models\Appointment;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.app', 'layouts.sidebar', 'dashboard.index', 'rooms.*'], function ($view) {
            try {
                $consulting = Appointment::with(['patient', 'room'])
                    ->where('status', Appointment::STATUS_IN_CONSULTATION)
                    ->orderBy('ApptTime')
                    ->get();
            } catch (\Throwable $e) {
                $consulting = collect();
            }
            $view->with('consultingAppointments', $consulting);
        });
    }
}
