<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    // Get a lists of permissions groups
    public static function groups(): array
    {
        return [
            'system' => __('System'),
            'communities' => __('Communities'),
            'stories' => __('Stories'),
            'comments' => __('Comments'),
            'tags' => __('Tags'),
            'pages' => __('Pages'),
            'reports' => __('Reports'),
            'users' => __('Users'),
        ];
    }

    // Generate permissions for the group name
    public static function generateGroup(string $item, ?string $group = null): void
    {
        self::query()->firstOrCreate([
            'name' => 'view_'.$item,
            'display_name' => __('View :item', ['item' => ucfirst($item)]),
        ]);

        self::query()->firstOrCreate([
            'name' => 'read_'.$item,
            'display_name' => __('Read :item', ['item' => ucfirst($item)]),
        ]);

        self::query()->firstOrCreate([
            'name' => 'add_'.$item,
            'display_name' => __('Add :item', ['item' => ucfirst($item)]),
        ]);

        self::query()->firstOrCreate([
            'name' => 'edit_'.$item,
            'display_name' => __('Edit :item', ['item' => ucfirst($item)]),
        ]);

        self::query()->firstOrCreate([
            'name' => 'delete_'.$item,
            'display_name' => __('Delete :item', ['item' => ucfirst($item)]),
        ]);
    }
}
