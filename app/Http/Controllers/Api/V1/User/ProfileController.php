<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;
    /**
     * Get authenticated user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $sim = $request->user();

        // Load relationships
        $sim->load(['organization', 'department']);

        return $this->successResponse([
            'name' => $sim->name,
            'mobile' => $sim->mobile,
            'organization_name' => $sim->organization ? $sim->organization->name : null,
            'department_name' => $sim->department ? $sim->department->name : null,
        ], 'Profile fetched successfully');
    }
}
