<?php

namespace App\Services;

use Yajra\DataTables\Facades\DataTables;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * Get all roles for listing
     */
    public function getAllRoles()
    {
        return Role::get();
    }

    /**
     * Get roles for DataTables
     */
    public function getRolesForDataTable()
    {
        $roles = $this->getAllRoles();

        return DataTables::of($roles)->addIndexColumn()->make(true);
    }

    /**
     * Get all permissions
     */
    public function getAllPermissions()
    {
        return Permission::all()
        ->groupBy('parent_name')
        ->map(function ($permissionsGroup) {
            return [
                'permissions' => $permissionsGroup
            ];
        });
    }

    /**
     * Create a new role with permissions
     */
    public function createRole(array $data)
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create(['name' => $data['name']]);
            $role->syncPermissions($data['permissions']);
            return $role;
        });
    }

    /**
     * Get role by ID
     */
    public function getRoleById($id)
    {
        return Role::findOrFail($id);
    }

    /**
     * Update role with permissions
     */
    public function updateRole($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $role = $this->getRoleById($id);
            $role->update(['name' => $data['name']]);
            $role->syncPermissions($data['permissions']);

            return $role;
        });
    }

    /**
     * Delete role by ID
     */
    public function deleteRole($id)
    {
        return DB::transaction(function () use ($id) {
            $role = $this->getRoleById($id);
            $role->delete();

            return true;
        });
    }
}
