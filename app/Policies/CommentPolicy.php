<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function create(User $user, Comment $comment)
    {
        return $user->can('add_comments');
    }

    public function edit(User $user, Comment $comment)
    {
        if ($user->can('edit_comments')) {
            return $user->id === (int) $comment->user_id || $user->isAdmin() || $user->isModerator();
        }
    }

    public function delete(User $user, Comment $comment)
    {
        if ($user->can('delete_comments')) {
            return $user->id === (int) $comment->user_id || $user->isAdmin() || $user->isModerator();
        }
    }

    public function report(User $user, Comment $comment)
    {
        return $user->id !== (int) $comment->user_id;
    }

    public function pinTop(User $user, Comment $comment)
    {
        return $user->isAdmin() || $user->isModerator();
    }
}
