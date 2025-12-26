<?php

namespace App\Http\Controllers;

use App\Models\Sim;
use App\Models\Organization;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SimController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Select only needed columns for better performance
        $query = Sim::select([
            'id', 'mobile', 'name', 'organization_id', 'department_id', 
            'created_at', 'updated_at'
        ]);

        // Role-based filtering (applied early for index usage)
        if ($user->role === 'organization') {
            // Organization role: show only SIMs from their organization
            $query->where('organization_id', $user->organization_id);
        }
        // Admin role: no filtering

        // Search with optimization
        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            if (strlen($search) >= 2) { // Only search if 2+ characters
                $query->where(function ($q) use ($search) {
                    $q->where('mobile', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            }
        }

        // Filter by organization (only for admin)
        if ($user->role === 'admin' && $request->has('organization_id') && $request->organization_id) {
            $query->where('organization_id', $request->organization_id);
        }

        // Filter by department
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        // Order by indexed column for better performance
        $query->orderBy('created_at', 'desc');

        // Pagination with limit and optimized eager loading
        $perPage = min($request->get('per_page', 10), 100); // Cap at 100 items
        $sims = $query->with([
            'organization:id,name',
            'department:id,name'
        ])->paginate($perPage);

        return response()->json($sims);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // For organization and manager roles, use their organization_id
        $organizationId = ($user->role === 'organization' || $user->role === 'manager') 
            ? $user->organization_id 
            : $request->organization_id;
        
        $rules = [
            'mobile' => 'required|string|max:20|unique:sims,mobile',
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
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

        $sim = Sim::create([
            'mobile' => $request->mobile,
            'name' => $request->name,
            'organization_id' => $organizationId,
            'department_id' => $request->department_id,
        ]);
        $sim->load(['organization', 'department']);

        return response()->json([
            'message' => 'SIM created successfully',
            'sim' => $sim
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sim $sim)
    {
        $sim->load(['organization', 'department']);
        return response()->json($sim);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sim $sim)
    {
        $user = auth()->user();
        
        // For organization role, use their organization_id
        $organizationId = ($user->role === 'organization') 
            ? $user->organization_id 
            : $request->organization_id;
        
        $rules = [
            'mobile' => 'required|string|max:20|unique:sims,mobile,' . $sim->id,
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
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

        $sim->update([
            'mobile' => $request->mobile,
            'name' => $request->name,
            'organization_id' => $organizationId,
            'department_id' => $request->department_id,
        ]);
        $sim->load(['organization', 'department']);

        return response()->json([
            'message' => 'SIM updated successfully',
            'sim' => $sim
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sim $sim)
    {
        $sim->delete();

        return response()->json([
            'message' => 'SIM deleted successfully'
        ]);
    }

    /**
     * Bulk delete sims.
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:sims,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        Sim::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'SIMs deleted successfully'
        ]);
    }

    /**
     * Get departments by organization.
     */
    public function getDepartments(Request $request)
    {
        $user = auth()->user();
        
        // For organization role, use their organization_id
        $organizationId = ($user->role === 'organization') 
            ? $user->organization_id 
            : $request->get('organization_id');
        
        if (!$organizationId) {
            return response()->json([]);
        }

        $departments = Department::where('organization_id', $organizationId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        return response()->json($departments);
    }

    /**
     * Import SIMs from CSV file
     */
    public function importCsv(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            
            // Get headers from first row
            $headers = array_map('trim', array_map('strtolower', $csvData[0]));
            unset($csvData[0]); // Remove header row
            
            // Validate headers based on role
            if ($user->role === 'admin') {
                $requiredHeaders = ['organization', 'department', 'mobile', 'name'];
            } else {
                $requiredHeaders = ['department', 'mobile', 'name'];
            }
            
            foreach ($requiredHeaders as $required) {
                if (!in_array($required, $headers)) {
                    return response()->json([
                        'message' => "Missing required column: {$required}",
                        'required_columns' => $requiredHeaders
                    ], 422);
                }
            }
            
            $imported = 0;
            $errors = [];
            $row = 1; // Start from 1 (after header)
            
            foreach ($csvData as $data) {
                $row++;
                
                if (count($data) < count($headers)) {
                    $errors[] = "Row {$row}: Insufficient columns";
                    continue;
                }
                
                // Map data to headers
                $rowData = array_combine($headers, $data);
                
                // Determine organization ID
                if ($user->role === 'admin') {
                    // Find organization by name
                    $organization = Organization::where('name', trim($rowData['organization']))->first();
                    if (!$organization) {
                        $errors[] = "Row {$row}: Organization '{$rowData['organization']}' not found";
                        continue;
                    }
                    $organizationId = $organization->id;
                } else {
                    $organizationId = $user->organization_id;
                }
                
                // Find department by name and organization
                $department = Department::where('name', trim($rowData['department']))
                    ->where('organization_id', $organizationId)
                    ->first();
                    
                if (!$department) {
                    $errors[] = "Row {$row}: Department '{$rowData['department']}' not found";
                    continue;
                }
                
                // Validate mobile number
                $mobile = trim($rowData['mobile']);
                if (empty($mobile)) {
                    $errors[] = "Row {$row}: Mobile number is required";
                    continue;
                }
                
                // Check if mobile already exists
                if (Sim::where('mobile', $mobile)->exists()) {
                    $errors[] = "Row {$row}: Mobile number '{$mobile}' already exists";
                    continue;
                }
                
                // Validate name
                $name = trim($rowData['name']);
                if (empty($name)) {
                    $errors[] = "Row {$row}: Name is required";
                    continue;
                }
                
                // Create SIM
                try {
                    Sim::create([
                        'mobile' => $mobile,
                        'name' => $name,
                        'organization_id' => $organizationId,
                        'department_id' => $department->id,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Row {$row}: " . $e->getMessage();
                }
            }
            
            return response()->json([
                'message' => "Import completed. {$imported} SIM(s) imported successfully.",
                'imported' => $imported,
                'errors' => $errors,
                'total_rows' => count($csvData)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing CSV file: ' . $e->getMessage()
            ], 500);
        }
    }
}
