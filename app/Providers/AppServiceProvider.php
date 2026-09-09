<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\CentralStock;
use App\Models\InPatient;
use App\Models\Medications;
use App\Models\MedicationOrder;
use App\Models\Patient;
use App\Models\Pharmaceutical;
use App\Models\Stf;
use App\Models\Wardrequisition;
use App\Policies\MedicationPolicy;
use App\Policies\PatientPolicy;
use App\Policies\RequisitionPolicy;
use App\Policies\StaffPolicy;
use App\Policies\SupplyPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Patient::class, PatientPolicy::class);
        Gate::policy(InPatient::class, PatientPolicy::class);
        Gate::policy(Appointment::class, PatientPolicy::class);
        Gate::policy(Stf::class, StaffPolicy::class);
        Gate::policy(Medications::class, MedicationPolicy::class);
        Gate::policy(MedicationOrder::class, MedicationPolicy::class);
        Gate::policy(Wardrequisition::class, RequisitionPolicy::class);
        Gate::policy(CentralStock::class, SupplyPolicy::class);
        Gate::policy(Pharmaceutical::class, SupplyPolicy::class);

        // @role('charge_nurse', 'doctor') ... @endrole
        Blade::if('role', fn (...$roles) => (bool) auth()->user()?->hasRole($roles));

        View::composer(['layouts.app', 'layouts.sidebar', 'dashboard.index', 'rooms.*'], function ($view) {
            try {
                $query = Appointment::with(['patient', 'room'])
                    ->where('status', Appointment::STATUS_IN_CONSULTATION)
                    ->orderBy('ApptTime');

                // Doctors only see their own consultations in the nav banner.
                $user = auth()->user();
                if ($user?->isClinician() && $user->stf_no !== null) {
                    $query->where('Consult_Stf_No', $user->stf_no);
                }

                $consulting = $query->get();
            } catch (\Throwable $e) {
                $consulting = collect();
            }
            $view->with('consultingAppointments', $consulting);
        });
    }
}
