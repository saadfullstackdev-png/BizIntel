<?php

namespace App\Services;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserService
{
    /**
     * Get all users for listing
     */
    public function getAllUsers()
    {
        return User::with('roles')->get();
    }

    /**
     * Get users for DataTables
     */
    public function getUsersForDataTable()
    {
        $users = User::with('roles');

        return DataTables::of($users)->addIndexColumn()->make(true);
    }

    /**
     * Create a new user with permissions
     */
    public function createUser(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data);
            $user->assignRole($data['role']);
            return $user;
        });
    }

    /**
     * Get user by ID
     */
    public function getUserById($id)
    {
        return User::with('roles')->findOrFail($id);
    }

    /**
     * Update user with permissions
     */
    public function updateUser($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $user = $this->getUserById($id);
            if(!empty($data['password'])){
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
            $user->update($data);
            $user->syncRoles($data['role']);

            return $user;
        });
    }

    /**
     * Delete user by ID
     */
    public function deleteUser($id)
    {
        return DB::transaction(function () use ($id) {
            $user = $this->getUserById($id);
            $user->delete();

            return true;
        });
    }
}
