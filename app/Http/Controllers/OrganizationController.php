<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    /**
     * Display a listing of organizations
     */
    public function index(Request $request): JsonResponse
    {
        $query = Organization::select([
            'id', 'name', 'email', 'mobile', 'description', 
            'status', 'created_at', 'updated_at'
        ]);

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

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Order by latest first for better performance
        $query->orderBy('created_at', 'desc');

        // Pagination with limit
        $perPage = min($request->get('per_page', 10), 50); // Cap at 50 items
        $organizations = $query->paginate($perPage);

        return response()->json($organizations);
    }

    /**
     * Store a newly created organization
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:organizations',
            'password' => 'required|string|min:6',
            'mobile' => 'required|string|max:20',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $organization = Organization::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile' => $request->mobile,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json($organization, 201);
    }

    /**
     * Display the specified organization
     */
    public function show(Organization $organization): JsonResponse
    {
        return response()->json($organization);
    }

    /**
     * Update the specified organization
     */
    public function update(Request $request, Organization $organization): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:organizations,email,' . $organization->id,
            'password' => 'nullable|string|min:6',
            'mobile' => 'required|string|max:20',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

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
            'description' => $request->description,
            'status' => $request->status,
        ];

        // Only update password if provided
        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $organization->update($updateData);

        return response()->json($organization);
    }

    /**
     * Update organization status
     */
    public function updateStatus(Request $request, Organization $organization): JsonResponse
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

        $organization->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Organization status updated successfully',
            'organization' => $organization
        ]);
    }

    /**
     * Remove the specified organization
     */
    public function destroy(Organization $organization): JsonResponse
    {
        $organization->delete();

        return response()->json([
            'message' => 'Organization deleted successfully'
        ]);
    }
}
