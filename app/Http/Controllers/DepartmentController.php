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
        $query = Department::select([
            'id', 'name', 'organization_id', 'created_at', 'updated_at'
        ]);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            if (strlen($search) >= 2) { // Only search if 2+ characters
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }
        }

        // Organization filter
        if ($request->has('organization_id') && $request->organization_id) {
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
        $organizations = Organization::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($organizations);
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $department = Department::create([
            'name' => $request->name,
            'organization_id' => $request->organization_id,
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $department->update([
            'name' => $request->name,
            'organization_id' => $request->organization_id,
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
