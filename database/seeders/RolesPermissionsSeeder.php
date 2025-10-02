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
            'delete'
        ];

        $permissions_by_role = [
            'administrator' => [
                'user-management.read',
                'user-management.create',
                'user-management.update',
                'user-management.delete',
                'user-management.restore',
                'user-management.export',
                'user-management.import',

                'role-management.read',
                'role-management.create',
                'role-management.update',
                'role-management.delete',
                'role-management.export',
                'role-management.import',

                'team-management.read',
                'team-management.create',
                'team-management.update',
                'team-management.delete',

                'transaction-management.read',
                'transaction-management.update',
                'transaction-management.delete',
                'transaction-management.follow-up',
                'transaction-management.restore',
                'transaction-management.export',
                'transaction-management.import',

                'member-management.read',
                'member-management.create',
                'member-management.update',
                'member-management.delete',
                'member-management.export',
                'member-management.restore',
                'member-management.import',

                'log-management.read',
                'log-management.export',

                'configuration-management.read',
                'configuration-management.create',
                'configuration-management.update',
                'configuration-management.delete',
                'configuration-management.export',
                'configuration-management.import',

                'export-management.export',

                'import-management.index',
                'import-management.update',

            ],
            'leader' => [
                'user-management.read',
                'user-management.create',
                'user-management.update',
                'user-management.export',
                'user-management.import',

                'transaction-management.read',
                'transaction-management.delete',
                'transaction-management.follow-up',
                'transaction-management.import',

                'member-management.read',
                'member-management.create',
                'member-management.update',
                'member-management.export',
                'member-management.restore',
            ],
            'marketing' => [
                'transaction-management.read',
                'transaction-management.import',
                'transaction-management.follow-up',

                'member-management.read',
                'member-management.create',
                'member-management.update',
            ]
        ];

        foreach ($permissions_by_role['administrator'] as $permission) {
            // foreach ($abilities as $ability) {
                // Permission::create(['name' => $ability . ' ' . $permission]);
                Permission::create(['name' => $permission]);
            // }
        }

        foreach ($permissions_by_role as $role => $permissions) {
            $full_permissions_list = [];
            // foreach ($abilities as $ability) {
                foreach ($permissions as $permission) {
                    $full_permissions_list[] = $permission;
                // }
            }
            Role::create(['name' => $role])->syncPermissions($full_permissions_list);
        }

        User::find(1)->assignRole('administrator');
        User::find(2)->assignRole('leader');
        User::whereIn('id', [3, 4, 5, 6, 7])->get()->each(function ($user) {
            $user->assignRole('marketing');
        });
    }
}
