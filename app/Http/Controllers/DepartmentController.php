<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = Department::select([
            'id', 'name', 'organization_id', 'created_at', 'updated_at'
        ]);

        // Role-based filtering
        if ($user->role === 'organization') {
            // Organization role: show only departments from their organization
            $query->where('organization_id', $user->organization_id);
        }
        // Admin and Organization roles: filtering applied above
        // Manager role: no access to departments module

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            if (strlen($search) >= 2) { // Only search if 2+ characters
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }
        }

        // Organization filter (only for admin)
        if ($user->role === 'admin' && $request->has('organization_id') && $request->organization_id) {
            $query->where('organization_id', $request->organization_id);
        }

        // Order by latest first for better performance
        $query->orderBy('created_at', 'desc');

        // Pagination with limit
        $perPage = min($request->get('per_page', 10), 50); // Cap at 50 items
        $departments = $query->with(['organization:id,name'])
                            ->paginate($perPage);

        return response()->json($departments);
    }

    /**
     * Get organizations for dropdown
     */
    public function getOrganizations(): JsonResponse
    {
        $user = auth()->user();
        
        $query = Organization::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name');
        
        // For organization and manager roles, only return their organization
        if ($user->role === 'organization' || $user->role === 'manager') {
            $query->where('id', $user->organization_id);
        }
        
        $organizations = $query->get();

        return response()->json($organizations);
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // For organization and manager roles, use their organization_id
        $organizationId = ($user->role === 'organization' || $user->role === 'manager') 
            ? $user->organization_id 
            : $request->organization_id;
        
        $rules = [
            'name' => 'required|string|max:255',
        ];
        
        // Only require organization_id if user is admin
        if ($user->role === 'admin') {
            $rules['organization_id'] = 'required|exists:organizations,id';
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $department = Department::create([
            'name' => $request->name,
            'organization_id' => $organizationId,
        ]);

        // Load organization relationship for response
        $department->load('organization:id,name');

        return response()->json($department, 201);
    }

    /**
     * Display the specified department
     */
    public function show(Department $department): JsonResponse
    {
        $department->load('organization:id,name');
        return response()->json($department);
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        $user = auth()->user();
        
        // For organization and manager roles, use their organization_id
        $organizationId = ($user->role === 'organization' || $user->role === 'manager') 
            ? $user->organization_id 
            : $request->organization_id;
        
        $rules = [
            'name' => 'required|string|max:255',
        ];
        
        // Only require organization_id if user is admin
        if ($user->role === 'admin') {
            $rules['organization_id'] = 'required|exists:organizations,id';
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $department->update([
            'name' => $request->name,
            'organization_id' => $organizationId,
        ]);

        // Load organization relationship for response
        $department->load('organization:id,name');

        return response()->json($department);
    }

    /**
     * Remove the specified department
     */
    public function destroy(Department $department): JsonResponse
    {
        $department->delete();

        return response()->json([
            'message' => 'Department deleted successfully'
        ]);
    }
}
