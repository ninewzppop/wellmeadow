<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wardrequisition;

class RequisitionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole([
            User::ROLE_MEDICAL_DIRECTOR,
            User::ROLE_CHARGE_NURSE,
            User::ROLE_SENIOR_NURSE,
            User::ROLE_STAFF_NURSE,
        ]);
    }

    public function view(User $user, Wardrequisition $requisition): bool
    {
        return $user->isMedicalDirector() || $this->inScope($user, $requisition);
    }

    public function create(User $user): bool
    {
        return $user->isMedicalDirector() || $user->hasRole(User::ROLE_CHARGE_NURSE);
    }

    public function update(User $user, Wardrequisition $requisition): bool
    {
        return $this->create($user) && $this->inScope($user, $requisition);
    }

    public function delete(User $user, Wardrequisition $requisition): bool
    {
        return $this->update($user, $requisition);
    }

    /** Approving deducts central stock — charge nurses approve their own ward. */
    public function approve(User $user, Wardrequisition $requisition): bool
    {
        return $this->update($user, $requisition);
    }

    public function receive(User $user, Wardrequisition $requisition): bool
    {
        return $this->update($user, $requisition);
    }

    private function inScope(User $user, Wardrequisition $requisition): bool
    {
        return $user->managesAllWards() || $requisition->Wd_No === $user->wardNo();
    }
}
