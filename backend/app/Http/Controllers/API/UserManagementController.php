<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users with pagination and filtering.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
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

        // Paginate results
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
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'phone_number' => ['nullable', 'string'],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id']
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        // Assign roles
        $user->roles()->attach($validated['roles']);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('roles')
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        $user = User::with(['roles', 'bookings', 'events'])->findOrFail($id);

        return response()->json([
            'user' => $user
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'password' => ['sometimes', 'string', 'min:8'],
            'phone_number' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean']
        ]);

        // Update password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get user statistics.
     */
    public function statistics()
    {
        // Total users
        $totalUsers = User::count();

        // Users by role
        $usersByRole = Role::withCount('users')->get();

        // New users in the last 30 days
        $newUsers = User::where('created_at', '>=', now()->subDays(30))->count();

        // Active users (with at least one booking)
        $activeUsers = User::whereHas('bookings')->count();

        // User growth over time (last 12 months)
        $userGrowth = DB::table('users')
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'total_users' => $totalUsers,
            'users_by_role' => $usersByRole,
            'new_users_last_30_days' => $newUsers,
            'active_users' => $activeUsers,
            'user_growth' => $userGrowth
        ]);
    }

    /**
     * Get all available roles.
     */
    public function roles()
    {
        $roles = Role::all();

        return response()->json([
            'roles' => $roles
        ]);
    }

    /**
     * Update user roles.
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
            'user' => $user->load('roles')
        ]);
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => $user->is_active ? 'User activated successfully' : 'User deactivated successfully',
            'user' => $user
        ]);
    }
    
    /**
     * Get system health information
     */
    public function systemHealth()
    {
        // In a real implementation, these would be actual metrics from monitoring tools
        $metrics = [
            'server' => [
                'cpu_usage' => rand(10, 90),
                'memory_usage' => rand(20, 85),
                'disk_usage' => rand(30, 80),
                'uptime' => rand(1, 30) . ' days',
            ],
            'database' => [
                'connections' => rand(5, 50),
                'query_time_avg' => rand(10, 200) . ' ms',
                'size' => rand(100, 1000) . ' MB',
            ],
            'application' => [
                'error_rate' => rand(0, 5) / 100,
                'response_time_avg' => rand(50, 500) . ' ms',
                'requests_per_minute' => rand(10, 1000),
            ],
            'cache' => [
                'hit_ratio' => rand(50, 95) . '%',
                'memory_usage' => rand(10, 80) . '%',
            ],
            'queue' => [
                'jobs_pending' => rand(0, 100),
                'failed_jobs' => rand(0, 10),
                'processed_last_hour' => rand(100, 1000),
            ]
        ];
        
        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'status' => 'healthy',
            'metrics' => $metrics
        ]);
    }
    
    /**
     * Get system logs
     */
    public function systemLogs(Request $request)
    {
        // In a real implementation, this would read from actual log files
        // For now, we'll return mock data
        
        $logTypes = ['error', 'warning', 'info', 'debug'];
        $components = ['auth', 'payment', 'booking', 'event', 'system'];
        
        $logs = [];
        
        for ($i = 0; $i < 50; $i++) {
            $timestamp = Carbon::now()->subMinutes(rand(1, 10000));
            $type = $logTypes[array_rand($logTypes)];
            $component = $components[array_rand($components)];
            
            $logs[] = [
                'id' => $i + 1,
                'timestamp' => $timestamp->toIso8601String(),
                'type' => $type,
                'component' => $component,
                'message' => "Sample {$type} log message from {$component} component",
                'details' => $type === 'error' ? 'Stack trace would go here' : null
            ];
        }
        
        // Sort by timestamp descending
        usort($logs, function($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });
        
        // Apply filters if provided
        if ($request->has('type')) {
            $logs = array_filter($logs, function($log) use ($request) {
                return $log['type'] === $request->type;
            });
        }
        
        if ($request->has('component')) {
            $logs = array_filter($logs, function($log) use ($request) {
                return $log['component'] === $request->component;
            });
        }
        
        // Paginate results
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);
        $offset = ($page - 1) * $perPage;
        
        $paginatedLogs = array_slice($logs, $offset, $perPage);
        $total = count($logs);
        
        return response()->json([
            'logs' => $paginatedLogs,
            'meta' => [
                'current_page' => (int)$page,
                'last_page' => ceil($total / $perPage),
                'per_page' => (int)$perPage,
                'total' => $total
            ]
        ]);
    }
    
    /**
     * Get audit logs for user actions
     */
    public function auditLogs(Request $request)
    {
        // In a real implementation, this would read from an audit_logs table
        // For now, we'll return mock data
        
        $actionTypes = ['login', 'logout', 'create', 'update', 'delete', 'export'];
        $resources = ['user', 'event', 'booking', 'payment', 'ticket', 'discount_code'];
        $userIds = range(1, 10);
        
        $logs = [];
        
        for ($i = 0; $i < 100; $i++) {
            $timestamp = Carbon::now()->subMinutes(rand(1, 20000));
            $actionType = $actionTypes[array_rand($actionTypes)];
            $resource = $resources[array_rand($resources)];
            $userId = $userIds[array_rand($userIds)];
            
            $logs[] = [
                'id' => $i + 1,
                'timestamp' => $timestamp->toIso8601String(),
                'user_id' => $userId,
                'user_name' => "User {$userId}",
                'action' => $actionType,
                'resource' => $resource,
                'resource_id' => rand(1, 1000),
                'ip_address' => '192.168.' . rand(0, 255) . '.' . rand(0, 255),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ];
        }
        
        // Sort by timestamp descending
        usort($logs, function($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });
        
        // Apply filters if provided
        if ($request->has('action')) {
            $logs = array_filter($logs, function($log) use ($request) {
                return $log['action'] === $request->action;
            });
        }
        
        if ($request->has('resource')) {
            $logs = array_filter($logs, function($log) use ($request) {
                return $log['resource'] === $request->resource;
            });
        }
        
        if ($request->has('user_id')) {
            $logs = array_filter($logs, function($log) use ($request) {
                return $log['user_id'] == $request->user_id;
            });
        }
        
        // Paginate results
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 15);
        $offset = ($page - 1) * $perPage;
        
        $paginatedLogs = array_slice($logs, $offset, $perPage);
        $total = count($logs);
        
        return response()->json([
            'audit_logs' => $paginatedLogs,
            'meta' => [
                'current_page' => (int)$page,
                'last_page' => ceil($total / $perPage),
                'per_page' => (int)$perPage,
                'total' => $total
            ]
        ]);
    }
}