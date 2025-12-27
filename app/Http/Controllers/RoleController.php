<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Constructor
     *
     * @param RoleService $roleService
     */
    public function __construct(private RoleService $roleService) {}


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->roleService->getRolesForDataTable();
        }

        return view('admin.user_management.roles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = $this->roleService->getAllPermissions();
        return view('admin.user_management.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        try {
            $this->roleService->createRole($request->validated());
            return response()->json(['success' => true, 'message' => 'Role Created Successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while creating role: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $role = $this->roleService->getRoleById($id);
            $role->permissions = $role->permissions->pluck('id')->toArray();
            $permissions = $this->roleService->getAllPermissions();

            return view('admin.user_management.roles.edit', compact('role', 'permissions'));
        } catch (\Exception $e) {
            return redirect()->route('roles.index')->with('error', 'Role not found.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, string $id)
    {
        try {
            $this->roleService->updateRole($id, $request->validated());
            return response()->json(['success' => true, 'message' => 'Role updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while updating role: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->roleService->deleteRole($id);
            return response()->json(['success' => true, 'message' => 'Role deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while deleting role.'], 500);
        }
    }
}
