<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = User::select([
            'id', 'name', 'email', 'mobile', 'organization_id', 'department_id',
            'status', 'role', 'created_at', 'updated_at'
        ])->whereIn('role', ['user', 'manager']);

        // Role-based filtering
        if ($user->role === 'organization') {
            // Organization role: show only users from their organization
            $query->where('organization_id', $user->organization_id);
        } elseif ($user->role === 'manager') {
            // Manager role: show only user role users from accessible departments
            $query->where('organization_id', $user->organization_id);
            $query->where('role', 'user'); // Only show user role users
            
            $accessibleDepartments = [];
            
            // Include manager's own department
            if ($user->department_id) {
                $accessibleDepartments[] = $user->department_id;
            }
            
            // Include allowed departments
            if ($user->allowed_department_ids) {
                $allowedDepartments = is_string($user->allowed_department_ids) 
                    ? json_decode($user->allowed_department_ids, true) 
                    : $user->allowed_department_ids;
                    
                if (!empty($allowedDepartments)) {
                    $accessibleDepartments = array_merge($accessibleDepartments, $allowedDepartments);
                }
            }
            
            if (!empty($accessibleDepartments)) {
                $query->whereIn('department_id', array_unique($accessibleDepartments));
            }
        }
        // Admin role: no filtering

        // Search functionality - optimized with full-text search
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            if (strlen($search) >= 2) { // Only search if 2+ characters
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%");
                });
            }
        }

        // Organization filter (only for admin)
        if ($user->role === 'admin' && $request->has('organization_id') && $request->organization_id) {
            $query->where('organization_id', $request->organization_id);
        }

        // Department filter
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        // Role filter
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Order by latest first for better performance
        $query->orderBy('created_at', 'desc');

        // Pagination with optimized relationship loading
        $perPage = min($request->get('per_page', 10), 50); // Cap at 50 items
        $users = $query->with(['organization:id,name', 'department:id,name'])
                      ->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Get organizations for dropdown
     */
    public function getOrganizations(): JsonResponse
    {
        $authUser = auth()->user();
        
        $query = Organization::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name');
        
        // For organization and manager roles, only return their organization
        if ($authUser->role === 'organization' || $authUser->role === 'manager') {
            $query->where('id', $authUser->organization_id);
        }
        
        $organizations = $query->get();

        return response()->json($organizations);
    }

    /**
     * Get departments for a specific organization
     */
    public function getDepartmentsByOrganization(Request $request): JsonResponse
    {
        $authUser = auth()->user();
        
        // For organization and manager roles, use their organization_id
        $organizationId = ($authUser->role === 'organization' || $authUser->role === 'manager') 
            ? $authUser->organization_id 
            : $request->organization_id;
        
        // Only validate organization_id if user is admin
        if ($authUser->role === 'admin') {
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
        }

        $query = Department::where('organization_id', $organizationId)
            ->select('id', 'name')
            ->orderBy('name');
        
        // For manager role, filter by accessible departments
        if ($authUser->role === 'manager') {
            $accessibleDepartments = [];
            
            // Include manager's own department
            if ($authUser->department_id) {
                $accessibleDepartments[] = $authUser->department_id;
            }
            
            // Include allowed departments
            if ($authUser->allowed_department_ids) {
                $allowedDepartments = is_string($authUser->allowed_department_ids) 
                    ? json_decode($authUser->allowed_department_ids, true) 
                    : $authUser->allowed_department_ids;
                    
                if (!empty($allowedDepartments)) {
                    $accessibleDepartments = array_merge($accessibleDepartments, $allowedDepartments);
                }
            }
            
            if (!empty($accessibleDepartments)) {
                $query->whereIn('id', array_unique($accessibleDepartments));
            }
        }
        
        $departments = $query->get();

        return response()->json($departments);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request): JsonResponse
    {
        $authUser = auth()->user();
        
        // For organization and manager roles, use their organization_id
        $organizationId = ($authUser->role === 'organization' || $authUser->role === 'manager') 
            ? $authUser->organization_id 
            : $request->organization_id;
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'mobile' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:active,inactive',
        ];
        
        // Manager can only create user role
        if ($authUser->role !== 'manager') {
            $rules['role'] = 'required|in:user,manager';
        }
        
        // Only require organization_id if user is admin
        if ($authUser->role === 'admin') {
            $rules['organization_id'] = 'required|exists:organizations,id';
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Manager can only create user role, force role to 'user'
        $userRole = ($authUser->role === 'manager') ? 'user' : ($request->role ?? 'user');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password),
            'role' => $userRole,
            'organization_id' => $organizationId,
            'department_id' => $request->department_id,
            'status' => $request->status ?? 'active',
        ]);

        // Load relationships for response
        $user->load(['organization:id,name', 'department:id,name']);

        return response()->json($user, 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['organization:id,name', 'department:id,name']);
        return response()->json($user);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $authUser = auth()->user();
        
        // For organization and manager roles, use their organization_id
        $organizationId = ($authUser->role === 'organization' || $authUser->role === 'manager') 
            ? $authUser->organization_id 
            : $request->organization_id;
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'mobile' => 'required|string|max:20',
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:user,manager',
            'department_id' => 'required|exists:departments,id',
            'status' => 'required|in:active,inactive',
        ];
        
        // Only require organization_id if user is admin
        if ($authUser->role === 'admin') {
            $rules['organization_id'] = 'required|exists:organizations,id';
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'role' => $request->role ?? 'user',
            'organization_id' => $organizationId,
            'department_id' => $request->department_id,
            'status' => $request->status,
        ];

        // Only update password if provided
        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);
        
        // Load relationships for response
        $user->load(['organization:id,name', 'department:id,name']);

        return response()->json($user);
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update(['status' => $request->status]);

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get data for assign SIMs page
     */
    public function getAssignSimsData(User $user): JsonResponse
    {
        $authUser = auth()->user();
        
        // Load user with relationships
        $user->load(['organization', 'department']);

        // Get departments based on auth user role
        $query = Department::where('organization_id', $user->organization_id)
            ->select('id', 'name', 'organization_id')
            ->orderBy('name');
        
        // For manager role, filter by accessible departments
        if ($authUser->role === 'manager') {
            $accessibleDepartments = [];
            
            // Include manager's own department
            if ($authUser->department_id) {
                $accessibleDepartments[] = $authUser->department_id;
            }
            
            // Include allowed departments
            if ($authUser->allowed_department_ids) {
                $allowedDepartments = is_string($authUser->allowed_department_ids) 
                    ? json_decode($authUser->allowed_department_ids, true) 
                    : $authUser->allowed_department_ids;
                    
                if (!empty($allowedDepartments)) {
                    $accessibleDepartments = array_merge($accessibleDepartments, $allowedDepartments);
                }
            }
            
            if (!empty($accessibleDepartments)) {
                $query->whereIn('id', array_unique($accessibleDepartments));
            }
        }
        
        $departments = $query->get();

        // Get currently assigned SIM IDs
        $assignedSimIds = \App\Models\UserSim::where('user_id', $user->id)
            ->pluck('sim_id')
            ->toArray();

        return response()->json([
            'user' => $user,
            'departments' => $departments,
            'assigned_sim_ids' => $assignedSimIds,
        ]);
    }

    /**
     * Get SIMs filtered by departments
     */
    public function getSimsByDepartments(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'department_ids' => 'required|array',
            'department_ids.*' => 'exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->user_id);
        
        // Get SIMs from selected departments and same organization
        $sims = \App\Models\Sim::whereIn('department_id', $request->department_ids)
            ->where('organization_id', $user->organization_id)
            ->select('id', 'mobile', 'name', 'department_id', 'organization_id')
            ->with('department:id,name')
            ->orderBy('mobile')
            ->get();

        return response()->json($sims);
    }

    /**
     * Get assigned SIMs for a user
     */
    public function getAssignedSims(User $user): JsonResponse
    {
        $assignedSims = \App\Models\Sim::whereHas('userSims', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->select('id', 'mobile', 'name', 'department_id')
        ->with('department:id,name')
        ->get();

        return response()->json($assignedSims);
    }

    /**
     * Get available SIMs for a user based on selected departments
     */
    public function getAvailableSims(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'department_ids' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $departmentIds = explode(',', $request->department_ids);

        // Get SIMs from selected departments and same organization
        $sims = \App\Models\Sim::whereIn('department_id', $departmentIds)
            ->where('organization_id', $user->organization_id)
            ->select('id', 'mobile', 'name', 'department_id', 'organization_id')
            ->with('department:id,name')
            ->orderBy('mobile')
            ->get();

        return response()->json($sims);
    }

    /**
     * Assign SIMs to a user
     */
    public function assignSims(Request $request, User $user): JsonResponse
    {
        $isManagerTarget = $user->role === 'manager';

        $rules = [
            'allowed_department_ids' => 'required|array|min:1',
            'allowed_department_ids.*' => 'exists:departments,id',
        ];

        if ($isManagerTarget) {
            // Managers should not have SIMs assigned.
            $rules['sim_ids'] = 'sometimes|array|max:0';
            $rules['sim_ids.*'] = 'prohibited';
        } else {
            $rules['sim_ids'] = 'required|array|min:1';
            $rules['sim_ids.*'] = 'exists:sims,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $selectedSims = collect();

        if (!$isManagerTarget) {
            // Validate that all selected SIMs belong to the selected departments
            $selectedSims = \App\Models\Sim::whereIn('id', $request->sim_ids)
                ->where('organization_id', $user->organization_id)
                ->get();

            foreach ($selectedSims as $sim) {
                if (!in_array($sim->department_id, $request->allowed_department_ids)) {
                    return response()->json([
                        'message' => 'SIM must belong to one of the selected departments',
                        'errors' => [
                            'sim_ids' => ['SIM ' . $sim->mobile . ' does not belong to the selected departments']
                        ]
                    ], 422);
                }
            }
        }

        // Begin transaction
        \DB::beginTransaction();
        
        try {
            // Update user's allowed departments
            $user->update([
                'allowed_department_ids' => json_encode($request->allowed_department_ids)
            ]);

            // Delete existing SIM assignments
            \App\Models\UserSim::where('user_id', $user->id)->delete();

            $userSimsData = [];
            if (!$isManagerTarget) {
                // Insert new SIM assignments
                foreach ($selectedSims as $sim) {
                    $userSimsData[] = [
                        'user_id' => $user->id,
                        'sim_id' => $sim->id,
                        'mobile' => $sim->mobile,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                \App\Models\UserSim::insert($userSimsData);
            }

            \DB::commit();

            return response()->json([
                'message' => $isManagerTarget ? 'Departments updated successfully' : 'SIMs assigned successfully',
                'assigned_count' => count($userSimsData)
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to assign SIMs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
