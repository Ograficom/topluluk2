<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PrivacyVisibility
{
    public const PUBLIC = 'public';
    public const FRIENDS = 'friends';
    public const PRIVATE = 'private';

    public const LEVELS = [
        self::PUBLIC,
        self::FRIENDS,
        self::PRIVATE,
    ];

    public static function apply(
        Builder $query,
        string $authorColumn,
        string $settingColumn,
        ?User $viewer,
    ): Builder {
        if ($viewer?->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $visibility) use ($authorColumn, $settingColumn, $viewer) {
            $visibility
                ->whereNull($authorColumn)
                ->orWhereExists(function ($users) use ($authorColumn, $settingColumn) {
                    $users->selectRaw('1')
                        ->from('users as privacy_users')
                        ->whereColumn('privacy_users.id', $authorColumn)
                        ->where('privacy_users.' . $settingColumn, self::PUBLIC);
                });

            if (! $viewer) {
                return;
            }

            $visibility
                ->orWhere($authorColumn, $viewer->id)
                ->orWhere(function (Builder $friends) use ($authorColumn, $settingColumn, $viewer) {
                    $friends
                        ->whereExists(function ($users) use ($authorColumn, $settingColumn) {
                            $users->selectRaw('1')
                                ->from('users as privacy_friend_users')
                                ->whereColumn('privacy_friend_users.id', $authorColumn)
                                ->where('privacy_friend_users.' . $settingColumn, self::FRIENDS);
                        })
                        ->whereExists(function ($outgoing) use ($authorColumn, $viewer) {
                            $outgoing->selectRaw('1')
                                ->from('follows as privacy_outgoing')
                                ->where('privacy_outgoing.follower_id', $viewer->id)
                                ->whereColumn('privacy_outgoing.followed_id', $authorColumn);
                        })
                        ->whereExists(function ($incoming) use ($authorColumn, $viewer) {
                            $incoming->selectRaw('1')
                                ->from('follows as privacy_incoming')
                                ->whereColumn('privacy_incoming.follower_id', $authorColumn)
                                ->where('privacy_incoming.followed_id', $viewer->id);
                        });
                });
        });
    }
}
