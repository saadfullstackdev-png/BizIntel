<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use Spatie\Permission\Models\Role;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Constructor
     *
     * @param UserService $userService
     */
    public function __construct(private UserService $userService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->userService->getUsersForDataTable();
        }

        $roles = Role::all();
        return view('admin.user_management.users.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        try {
            $this->userService->createUser($request->validated());
            return response()->json(['success' => true, 'message' => 'User Created Successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while creating user: ' . $e->getMessage()], 500);
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
        $user = $this->userService->getUserById($id);
        $fields = $user->getFillable();
        return response()->json(['success' => true, 'user' => $user, 'fields' => $fields]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
        try {
            $this->userService->updateUser($id, $request->validated());
            return response()->json(['success' => true, 'message' => 'User Updated Successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while updating user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Update user status (active/inactive)
     */
    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'is_active' => 'required|boolean',
            ]);

            $user = $this->userService->getUserById($request->user_id);
            $user->update(['is_active' => $request->is_active]);

            $status = $request->is_active ? 'activated' : 'deactivated';
            return response()->json(['success' => true, 'message' => "User {$status} successfully!"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
