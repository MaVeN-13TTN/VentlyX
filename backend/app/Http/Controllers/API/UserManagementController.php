<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    /**
     * Get all users with pagination and filtering options
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role')) {
            $role = $request->role;
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Apply sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $users = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'users' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total()
            ]
        ]);
    }

    /**
     * Get details of a specific user
     */
    public function show(string $id)
    {
        $user = User::with(['roles', 'bookings.event', 'organizedEvents'])->findOrFail($id);

        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()],
            'phone_number' => ['nullable', 'string', 'max:15'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id']
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone_number' => $validated['phone_number'] ?? null,
            ]);

            // Assign roles
            if (isset($validated['roles'])) {
                $user->roles()->attach($validated['roles']);
            }

            DB::commit();

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user->load('roles')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update user details
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'phone_number' => ['nullable', 'string', 'max:15'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:roles,id'],
            'password' => ['sometimes', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()]
        ]);

        try {
            DB::beginTransaction();

            $updateData = collect($validated)
                ->except(['roles', 'password'])
                ->filter(function ($value) {
                    return $value !== null;
                })
                ->toArray();

            if (isset($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            // Update roles if provided
            if (isset($validated['roles'])) {
                $user->roles()->sync($validated['roles']);
            }

            DB::commit();

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user->fresh()->load('roles')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a user
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Users with admin role cannot be deleted
        if ($user->hasRole('Admin')) {
            return response()->json([
                'message' => 'Cannot delete admin users'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get user statistics and metrics
     */
    public function statistics()
    {
        $stats = [
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'role_distribution' => $this->getRoleDistribution(),
            'registration_trend' => $this->getRegistrationTrend(),
        ];

        return response()->json($stats);
    }

    /**
     * Get all available roles
     */
    public function roles()
    {
        $roles = Role::all();
        return response()->json(['roles' => $roles]);
    }

    /**
     * Update user roles
     */
    public function updateRoles(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id']
        ]);

        $user->roles()->sync($validated['roles']);

        return response()->json([
            'message' => 'User roles updated successfully',
            'user' => $user->fresh()->load('roles')
        ]);
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(string $id)
    {
        $user = User::findOrFail($id);

        // Admins cannot be deactivated
        if ($user->hasRole('Admin') && $user->is_active) {
            return response()->json([
                'message' => 'Admin users cannot be deactivated'
            ], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "User {$status} successfully",
            'is_active' => $user->is_active
        ]);
    }

    /**
     * Get distribution of users by role
     */
    protected function getRoleDistribution()
    {
        return DB::table('roles')
            ->select('roles.name', DB::raw('COUNT(role_user.user_id) as count'))
            ->leftJoin('role_user', 'roles.id', '=', 'role_user.role_id')
            ->groupBy('roles.name')
            ->get();
    }

    /**
     * Get registration trend data
     */
    protected function getRegistrationTrend()
    {
        return DB::table('users')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subMonths(3))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
