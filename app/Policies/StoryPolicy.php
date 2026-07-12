<?php

namespace App\Policies;

use App\Models\Story;
use App\Models\User;

class StoryPolicy
{
    public function edit(User $user, Story $story)
    {
        if ($user->can('edit_stories')) {
            return $user->id === (int) $story->user_id || $user->isAdmin();
        }
    }

    public function pin(User $user, Story $story)
    {
        if ($user->can('edit_stories') && $story->isPublished()) {
            return $user->id === (int) $story->user_id;
        }
    }

    public function delete(User $user, Story $story)
    {
        if ($user->can('delete_stories')) {
            return $user->id === (int) $story->user_id || $user->isAdmin();
        }
    }

    public function save(User $user, Story $story)
    {
        return $user->id !== (int) $story->user_id;
    }

    public function report(User $user, Story $story)
    {
        return $user->id !== (int) $story->user_id;
    }

    public function moderate(User $user, Story $story)
    {
        return $user->isAdmin() || $user->isModerator();
    }
}
