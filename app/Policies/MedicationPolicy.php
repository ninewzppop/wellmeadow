<?php

namespace App\Policies;

use App\Models\MedicationOrder;
use App\Models\Medications;
use App\Models\User;

class MedicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::CARE_ROLES);
    }

    public function view(User $user, MedicationOrder|Medications $record): bool
    {
        if ($user->isMedicalDirector()) {
            return true;
        }

        if (! $user->hasRole(User::CARE_ROLES)) {
            return false;
        }

        // Own prescriptions are always visible to the prescriber.
        if ($record->Stf_No !== null && $record->Stf_No === $user->stf_no) {
            return true;
        }

        $ids = $user->accessiblePatientIds();

        return $ids !== null
            ? in_array($record->Pt_No, $ids, true)
            : $this->patientInOwnWard($user, $record->Pt_No);
    }

    /** Prescribing a new order (room queue modal). */
    public function create(User $user): bool
    {
        return $user->hasRole([
            User::ROLE_MEDICAL_DIRECTOR,
            User::ROLE_CHARGE_NURSE,
            User::ROLE_DOCTOR,
            User::ROLE_CONSULTANT,
        ]);
    }

    /** Pharmacy dispense / cancel — store-side action. */
    public function dispense(User $user, MedicationOrder|Medications $record): bool
    {
        if ($user->isMedicalDirector()) {
            return true;
        }

        return $user->hasRole(User::ROLE_CHARGE_NURSE) && $this->view($user, $record);
    }

    private function patientInOwnWard(User $user, ?string $ptNo): bool
    {
        if ($ptNo === null || $user->managesAllWards()) {
            return $user->managesAllWards();
        }

        return \App\Models\InPatient::where('Pt_No', $ptNo)
            ->whereHas('bed', fn ($q) => $q->where('Wd_No', $user->wardNo()))
            ->exists();
    }
}
