<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $abilities = [
            'read',
            'write',
            'create',
        ];

        $permissions_by_role = [
            'administrator' => [
                'user management',
                'transaction management',
                'member management',
                'log management',
                'reporting management',
                'configuration management'
            ],
            'leader' => [
                'user management',
                'transaction management',
                'member management',
                'log management',
                'reporting management',
                'configuration management'
            ],
            'marketing' => [
                'member management',
                'reporting management'
            ]
        ];

        foreach ($permissions_by_role['administrator'] as $permission) {
            foreach ($abilities as $ability) {
                Permission::create(['name' => $ability . ' ' . $permission]);
            }
        }

        foreach ($permissions_by_role as $role => $permissions) {
            $full_permissions_list = [];
            foreach ($abilities as $ability) {
                foreach ($permissions as $permission) {
                    $full_permissions_list[] = $ability . ' ' . $permission;
                }
            }
            Role::create(['name' => $role])->syncPermissions($full_permissions_list);
        }

        User::find(1)->assignRole('administrator');
        User::find(2)->assignRole('leader');
        User::find(3)->assignRole('marketing');
    }
}
