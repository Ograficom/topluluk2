<?php

namespace App\Policies;

use App\Models\Community;
use App\Models\User;

class CommunityPolicy
{
    public function edit(User $user, Community $community)
    {
        return $user->id === (int) $community->user_id || $user->isAdmin();
    }
}
