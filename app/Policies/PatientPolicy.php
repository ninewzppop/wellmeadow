<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\InPatient;
use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Area gate: only care roles may open patient areas at all.
     * (personnel_officer and auxiliary never see patient data.)
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::CARE_ROLES);
    }

    public function create(User $user): bool
    {
        return $user->hasRole([
            User::ROLE_MEDICAL_DIRECTOR,
            User::ROLE_CHARGE_NURSE,
            User::ROLE_DOCTOR,
            User::ROLE_CONSULTANT,
        ]);
    }

    public function view(User $user, Patient|InPatient|Appointment $record): bool
    {
        if ($user->isMedicalDirector()) {
            return true;
        }

        if (! $user->hasRole(User::CARE_ROLES)) {
            return false;
        }

        $ptNo = $record instanceof Patient ? $record->Pt_No : $record->Pt_No;

        if ($user->isClinician()) {
            return in_array($ptNo, $user->accessiblePatientIds() ?? [], true);
        }

        // Ward roles: patient linked to a bed in own ward.
        // Charge nurses additionally see unplaced waiting-list rows (Bed_No null),
        // which carry no ward until a bed is assigned.
        $inWard = InPatient::where('Pt_No', $ptNo)
            ->whereHas('bed', fn ($q) => $q->where('Wd_No', $user->wardNo()))
            ->exists();

        if ($inWard) {
            return true;
        }

        return $user->hasRole(User::ROLE_CHARGE_NURSE)
            && InPatient::where('Pt_No', $ptNo)->whereNull('Bed_No')->exists();
    }

    public function update(User $user, Patient|InPatient|Appointment $record): bool
    {
        if ($user->isMedicalDirector()) {
            return true;
        }

        // Doctors manage visits/medications, not the patient master record.
        return $user->hasRole(User::ROLE_CHARGE_NURSE) && $this->view($user, $record);
    }

    public function delete(User $user, Patient|InPatient|Appointment $record): bool
    {
        return $this->update($user, $record);
    }
}
