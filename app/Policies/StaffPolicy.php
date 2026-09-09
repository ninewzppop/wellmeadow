<?php

namespace App\Policies;

use App\Models\Stf;
use App\Models\User;

class StaffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole([
            User::ROLE_MEDICAL_DIRECTOR,
            User::ROLE_PERSONNEL_OFFICER,
            User::ROLE_DOCTOR,
            User::ROLE_CONSULTANT,
        ]);
    }

    public function view(User $user, Stf $staff): bool
    {
        if ($user->isMedicalDirector() || $user->hasRole(User::ROLE_PERSONNEL_OFFICER)) {
            return true;
        }

        // Doctors see staff allocated to their own ward (salary hidden in views).
        return $user->isClinician()
            && ($user->managesAllWards() || $staff->Alloc_Wd_No === $user->wardNo());
    }

    public function create(User $user): bool
    {
        return $user->hasRole([
            User::ROLE_MEDICAL_DIRECTOR,
            User::ROLE_PERSONNEL_OFFICER,
        ]);
    }

    public function update(User $user, Stf $staff): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Stf $staff): bool
    {
        return $this->create($user);
    }
}
