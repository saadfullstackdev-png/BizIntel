<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'Dashboard' => ['dashboard'],

            'User Management' => ['view-users', 'create-users', 'edit-users'],
            'Role Management' => ['view-roles', 'create-roles', 'edit-roles', 'delete-roles'],
        ];

        $data = [];
        foreach ($permissions as $parent => $permission) {
            foreach ($permission as $value) {
                $display_name = ucwords(str_replace('-', ' ', $value));
                // $display_name = ucwords(collect(explode('-', $value))
                //     ->slice(0, -1)
                //     ->implode(' '));
                if (!Permission::where('parent_name', $parent)->where('display_name', $display_name)->where('name', $value)->where('guard_name', 'web')->exists()) {
                    $data[] = [
                        'parent_name' => $parent,
                        'display_name' => $display_name,
                        'name' => $value,
                        'guard_name' => 'web',
                        'created_at' => now(),
                    ];
                }
            }
        }
        Permission::insert($data);
    }
}
