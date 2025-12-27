<?php

use Illuminate\Database\Seeder;

class PackageReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $MainPermission = Permission::create([
            'title' => 'Package Reports',
            'name' => 'package_reports_manage',
            'guard_name' => 'web',
            'main_group' => 1,
            'parent_id' => 0,
        ]);
        Permission::insert([
            [
                'title' => 'Package Sale Count',
                'name' => 'package_reports_package_sale_count',
                'guard_name' => 'web',
                'main_group' => 0,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'parent_id' => $MainPermission->id,
            ],
        ]);

        $role = Role::findOrFail(1);

        // Assign Permission to 'administrator' role
        $role->givePermissionTo('package_reports_manage');
        $role->givePermissionTo('package_reports_package_sale_count');
    }
}
