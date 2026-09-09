<?php

namespace App\Policies;

use App\Models\CentralStock;
use App\Models\Pharmaceutical;
use App\Models\User;

class SupplyPolicy
{
    /**
     * Central stock/pharmacy are store-level data: readable by care roles
     * (doctors need the drug list for prescribing), never by
     * personnel_officer or auxiliary.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::CARE_ROLES);
    }

    public function view(User $user, CentralStock|Pharmaceutical $item): bool
    {
        return $user->hasRole(User::CARE_ROLES);
    }

    /** Stock master edits + restock/adjust stay with the medical director. */
    public function manage(User $user, CentralStock|Pharmaceutical $item): bool
    {
        return $user->isMedicalDirector();
    }
}
