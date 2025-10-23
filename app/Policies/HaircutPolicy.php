<?php

namespace App\Policies;

use App\Models\Haircut;
use App\Models\User;

class HaircutPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Todos pueden ver la lista de cortes
    }

    public function view(User $user, Haircut $haircut): bool
    {
        // Solo cortes publicados o admin puede ver todos
        return $haircut->is_published || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Haircut $haircut): bool
    {
        return $user->isAdmin() && $haircut->admin_id === $user->id;
    }

    public function delete(User $user, Haircut $haircut): bool
    {
        return $user->isAdmin() && $haircut->admin_id === $user->id;
    }

    public function publish(User $user, Haircut $haircut): bool
    {
        return $user->isAdmin() && $haircut->admin_id === $user->id;
    }

    public function manageImages(User $user, Haircut $haircut): bool
    {
        return $user->isAdmin() && $haircut->admin_id === $user->id;
    }
}