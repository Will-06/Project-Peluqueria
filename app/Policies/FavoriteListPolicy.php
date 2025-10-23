<?php

namespace App\Policies;

use App\Models\FavoriteList;
use App\Models\User;

class FavoriteListPolicy
{
    public function view(User $user, FavoriteList $favoriteList): bool
    {
        return $user->id === $favoriteList->user_id || !$favoriteList->is_private;
    }

    public function update(User $user, FavoriteList $favoriteList): bool
    {
        return $user->id === $favoriteList->user_id;
    }

    public function delete(User $user, FavoriteList $favoriteList): bool
    {
        return $user->id === $favoriteList->user_id;
    }
}