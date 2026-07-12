<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function ban(User $user, User $subject): bool
    {
        return ($user->isAdmin() && ! $subject->isAdmin()) ||
            ($user->isModerator() && ! $subject->isAdmin() && ! $subject->isModerator());
    }

    public function delete(User $user, User $subject): bool
    {
        return ($user->isAdmin() || $user->is($subject)) && ! $subject->isAdmin();
    }

    public function owner(User $user, User $subject): bool
    {
        return $user->is($subject);
    }

    public function edit(User $user, User $subject): bool
    {
        return $user->id === (int) $subject->id || $user->isAdmin();
    }

    public function follow(User $user, User $subject): bool
    {
        return $user->id !== (int) $subject->id;
    }

    public function report(User $user, User $subject): bool
    {
        return $user->id !== (int) $subject->id;
    }

    public function block(User $user, User $subject): bool
    {
        return $user->id !== (int) $subject->id;
    }

    public function cp(User $user): bool
    {
        return $user->isAdmin();
    }
}
