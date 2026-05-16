<?php

namespace App\Http\Controllers;

use App\Models\Sim;
use App\Models\Organization;
use App\Models\Team;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'id', 'mobile', 'name', 'organization_id', 'team_id', 
            'status', 'created_at', 'updated_at'
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

        // Filter by team
        if ($request->has('team_id') && $request->team_id) {
            $query->where('team_id', $request->team_id);
        }

        // Order by indexed column for better performance
        $query->orderBy('created_at', 'desc');

        // Pagination with limit and optimized eager loading
        $perPage = min($request->get('per_page', 10), 100); // Cap at 100 items
        $sims = $query->with([
            'organization:id,name',
            'team:id,name'
        ])->paginate($perPage);

        return response()->json($sims);
    }

    /**
     * Update SIM status
     */
    public function updateStatus(Request $request, Sim $sim): JsonResponse
    {
        $user = auth()->user();

        // Organization role can only update SIMs in their organization
        if ($user?->role === 'organization' && (int) $sim->organization_id !== (int) $user->organization_id) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // When activating a SIM, verify subscription is active and limit allows it.
        if ($request->status === 'active' && $sim->status !== 'active') {
            $organizationId = (int) $sim->organization_id;

            if (! SubscriptionService::isSubscriptionActive($organizationId)) {
                return response()->json([
                    'message' => 'Your subscription has expired. Please contact administrator to renew.',
                    'code'    => 'SUBSCRIPTION_EXPIRED',
                ], 403);
            }

            if (! SubscriptionService::canAddOrActivateSim($organizationId)) {
                return response()->json([
                    'message'       => 'SIM limit reached. Select an active SIM to deactivate first.',
                    'code'          => 'SIM_LIMIT_REACHED',
                    'limit_reached' => true,
                    'active_sims'   => SubscriptionService::getActiveSims($organizationId),
                ], 422);
            }
        }

        $sim->update(['status' => $request->status]);

        // Revoke only this SIM's tokens so the mobile app is immediately logged out
        if ($request->status === 'inactive') {
            $sim->tokens()->delete();
        }

        return response()->json([
            'message' => 'SIM status updated successfully',
            'sim'     => $sim->fresh()->load(['organization:id,name', 'team:id,name']),
        ]);
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
            'mobile' => ['required', 'digits:10', 'unique:sims,mobile'],
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
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

        if (!$this->isTeamInOrganization((int) $request->team_id, (int) $organizationId)) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'team_id' => ['The selected team does not belong to the selected organization.']
                ]
            ], 422);
        }

        // Enforce subscription + SIM limit before creating a new SIM.
        if (! SubscriptionService::isSubscriptionActive((int) $organizationId)) {
            return response()->json([
                'message' => 'Your subscription has expired. Please contact administrator to renew.',
                'code'    => 'SUBSCRIPTION_EXPIRED',
            ], 403);
        }

        if (! SubscriptionService::canAddOrActivateSim((int) $organizationId)) {
            return response()->json([
                'message' => 'SIM limit reached. Please upgrade your plan.',
                'code'    => 'SIM_LIMIT_REACHED',
            ], 422);
        }

        $sim = Sim::create([
            'mobile'          => $request->mobile,
            'name'            => $request->name,
            'organization_id' => $organizationId,
            'team_id'         => $request->team_id,
        ]);
        $sim->load(['organization', 'team']);

        return response()->json([
            'message' => 'SIM created successfully',
            'sim'     => $sim,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sim $sim)
    {
        $user = auth()->user();
        if (!$this->canAccessSim($user, $sim)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $sim->load(['organization', 'team']);
        return response()->json($sim);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sim $sim)
    {
        $user = auth()->user();
        if (!$this->canAccessSim($user, $sim)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        // For organization role, use their organization_id
        $organizationId = ($user->role === 'organization') 
            ? $user->organization_id 
            : $request->organization_id;
        
        $rules = [
            'mobile' => ['required', 'digits:10', 'unique:sims,mobile,' . $sim->id],
            'name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
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

        if (!$this->isTeamInOrganization((int) $request->team_id, (int) $organizationId)) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'team_id' => ['The selected team does not belong to the selected organization.']
                ]
            ], 422);
        }

        $sim->update([
            'mobile' => $request->mobile,
            'name' => $request->name,
            'organization_id' => $organizationId,
            'team_id' => $request->team_id,
        ]);
        $sim->load(['organization', 'team']);

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
        $user = auth()->user();
        if (!$this->canAccessSim($user, $sim)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

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
        $user = auth()->user();

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

        if ($user->role !== 'admin') {
            $totalRequested = count($request->ids);
            $authorizedCount = Sim::whereIn('id', $request->ids)
                ->where('organization_id', $user->organization_id)
                ->count();

            if ($authorizedCount !== $totalRequested) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        Sim::whereIn('id', $request->ids)->delete();

        return response()->json([
            'message' => 'SIMs deleted successfully'
        ]);
    }

    /**
     * Get teams by organization.
     */
    public function getTeams(Request $request)
    {
        $user = auth()->user();
        
        // For organization role, use their organization_id
        $organizationId = ($user->role === 'organization') 
            ? $user->organization_id 
            : $request->get('organization_id');
        
        if (!$organizationId) {
            return response()->json([]);
        }

        $teams = Team::where('organization_id', $organizationId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        return response()->json($teams);
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
                $requiredHeaders = ['organization', 'team', 'mobile', 'name'];
            } else {
                $requiredHeaders = ['team', 'mobile', 'name'];
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
                
                // Find team by name and organization
                $team = Team::where('name', trim($rowData['team']))
                    ->where('organization_id', $organizationId)
                    ->first();
                    
                if (!$team) {
                    $errors[] = "Row {$row}: Team '{$rowData['team']}' not found";
                    continue;
                }
                
                // Validate mobile number
                $mobile = trim($rowData['mobile']);
                if (empty($mobile)) {
                    $errors[] = "Row {$row}: Mobile number is required";
                    continue;
                }

                if (!preg_match('/^\d{10}$/', $mobile)) {
                    $errors[] = "Row {$row}: Mobile number '{$mobile}' must be exactly 10 digits";
                    continue;
                }
                
                // Check if mobile already exists
                if (Sim::where('mobile', $mobile)->exists()) {
                    $errors[] = "Row {$row}: Mobile number '{$mobile}' already exists";
                    continue;
                }
                
                // Validate name — strip formula injection chars (CSV injection / CWE-1236)
                $name = $this->sanitizeCsvField(trim($rowData['name'] ?? ''));
                if (empty($name)) {
                    $errors[] = "Row {$row}: Name is required";
                    continue;
                }
                
                // Check subscription + SIM limit before creating each SIM.
                if (! SubscriptionService::isSubscriptionActive((int) $organizationId)) {
                    $errors[] = "Row {$row}: Subscription expired for this organization.";
                    continue;
                }

                if (! SubscriptionService::canAddOrActivateSim((int) $organizationId)) {
                    $errors[] = "Row {$row}: SIM limit reached for this organization. Skipping remaining SIMs.";
                    break; // No point continuing — the limit is full
                }

                // Create SIM
                try {
                    Sim::create([
                        'mobile' => $mobile,
                        'name' => $name,
                        'organization_id' => $organizationId,
                        'team_id' => $team->id,
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
            \Illuminate\Support\Facades\Log::error('SIM CSV import failed', [
                'error' => $e->getMessage(),
                'ip'    => request()->ip(),
            ]);

            return response()->json(['message' => 'Error processing CSV file. Please check the file format.'], 500);
        }
    }

    /**
     * Strip formula-injection characters to prevent CSV injection (CWE-1236).
     */
    private function sanitizeCsvField(string $value): string
    {
        $value = strip_tags($value);

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r", "\n"], true)) {
            $value = "'" . $value;
        }

        return mb_substr($value, 0, 255);
    }

    // -------------------------------------------------------------------------
    // SIM swap — atomically deactivate one SIM and activate another
    // -------------------------------------------------------------------------

    /**
     * POST /sims/swap
     *
     * Body:
     *   activate_sim_id   int  – SIM to activate   (must belong to the org, currently inactive)
     *   deactivate_sim_id int  – SIM to deactivate (must currently be active, same org)
     *
     * Used when the SIM limit is full and the user wants to swap which SIM is
     * active without upgrading their plan.
     */
    public function swap(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'activate_sim_id'   => 'required|integer|exists:sims,id',
            'deactivate_sim_id' => 'required|integer|exists:sims,id|different:activate_sim_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $toActivate   = Sim::findOrFail($request->activate_sim_id);
        $toDeactivate = Sim::findOrFail($request->deactivate_sim_id);

        // Determine the organization scope the user is allowed to work with.
        $organizationId = $user->role === 'admin'
            ? (int) $toActivate->organization_id
            : (int) $user->organization_id;

        // Both SIMs must belong to the same organization.
        if ((int) $toActivate->organization_id !== $organizationId ||
            (int) $toDeactivate->organization_id !== $organizationId) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($toDeactivate->status !== 'active') {
            return response()->json([
                'message' => 'The SIM selected for deactivation is not currently active.',
            ], 422);
        }

        if ($toActivate->status === 'active') {
            return response()->json([
                'message' => 'The SIM you want to activate is already active.',
            ], 422);
        }

        if (! SubscriptionService::isSubscriptionActive($organizationId)) {
            return response()->json([
                'message' => 'Your subscription has expired. Please contact administrator to renew.',
                'code'    => 'SUBSCRIPTION_EXPIRED',
            ], 403);
        }

        DB::transaction(function () use ($toActivate, $toDeactivate) {
            $toDeactivate->update(['status' => 'inactive']);
            $toActivate->update(['status' => 'active']);
        });

        return response()->json([
            'message'     => 'SIM swap completed successfully.',
            'activated'   => $toActivate->fresh()->load(['organization:id,name', 'team:id,name']),
            'deactivated' => $toDeactivate->fresh()->load(['organization:id,name', 'team:id,name']),
        ]);
    }

    private function canAccessSim($actor, Sim $sim): bool
    {
        if ($actor->role === 'admin') {
            return true;
        }

        return (int) $sim->organization_id === (int) $actor->organization_id;
    }

    private function isTeamInOrganization(int $teamId, int $organizationId): bool
    {
        return Team::query()
            ->where('id', $teamId)
            ->where('organization_id', $organizationId)
            ->exists();
    }
}
