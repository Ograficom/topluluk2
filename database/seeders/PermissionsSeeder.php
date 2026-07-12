<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        Permission::firstOrCreate(['name' => 'access_admin_dashboard'], ['display_name' => __('Access Admin Dashboard')]);
        Permission::firstOrCreate(['name' => 'access_settings'], ['display_name' => __('Access Settings')]);
        Permission::firstOrCreate(['name' => 'access_contact_form_messages'], ['display_name' => __('Access Contact Form Messages')]);
        Permission::firstOrCreate(['name' => 'add_featured_story'], ['display_name' => __('Mark story to featured')]);

        Permission::generateGroup('communities');
        Permission::generateGroup('stories');
        Permission::generateGroup('comments');
        Permission::generateGroup('tags');
        Permission::generateGroup('pages');
        Permission::generateGroup('reports');
        Permission::generateGroup('users');

        $administrator = Role::query()->where('name', 'administrator')->firstOrFail();
        $permissions = Permission::all();
        $administrator->permissions()->sync($permissions->pluck('id')->all());

        $roleModerator = Role::findByName('moderator');
        $roleModerator->givePermissionTo('view_stories', 'read_stories', 'add_stories', 'edit_stories', 'delete_stories', 'view_comments', 'read_comments', 'add_comments', 'edit_comments', 'delete_comments', 'add_communities');

        $roleEditor = Role::findByName('editor');
        $roleEditor->givePermissionTo('view_stories', 'read_stories', 'add_stories', 'edit_stories', 'delete_stories', 'view_comments', 'read_comments', 'add_comments', 'edit_comments', 'delete_comments', 'add_communities', 'add_featured_story');

        $roleAuthor = Role::findByName('author');
        $roleAuthor->givePermissionTo('view_stories', 'read_stories', 'add_stories', 'edit_stories', 'delete_stories', 'view_comments', 'read_comments', 'add_comments', 'edit_comments', 'delete_comments', 'add_communities');

        $roleAuthor = Role::findByName('user');
        $roleAuthor->givePermissionTo('view_stories', 'read_stories', 'view_comments', 'read_comments', 'add_comments', 'edit_comments', 'delete_comments');

        $roleAuthor = Role::findByName('readonly');
        $roleAuthor->givePermissionTo('view_stories', 'read_stories', 'view_comments', 'read_comments');
    }
}
