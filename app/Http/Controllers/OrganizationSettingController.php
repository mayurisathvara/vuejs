<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationSettingController extends Controller
{
    private const CALLBACK_WINDOW_OPTIONS = [24, 48, 72, 96];

    private const DATE_FORMAT_OPTIONS = [
        'Y-m-d',
        'd-m-Y',
        'm-d-Y',
        'd/m/Y',
        'm/d/Y',
        'Y/m/d',
    ];

    public function show(Organization $organization): JsonResponse
    {
        $user = request()->user();
        if ($user && $user->role === 'organization') {
            $userOrgId = $user->organization_id ?? optional($user->organization)->id;
            if ((int) $userOrgId !== (int) $organization->id) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        $settings = OrganizationSetting::firstOrCreate(
            ['organization_id' => $organization->id],
            [
                'callback_window_hours' => 48,
                'date_formate' => 'Y-m-d',
                'enable_manager_role' => false,
                'enable_working_hours' => false,
                'working_hours' => null,
            ]
        );

        return response()->json([
            'settings' => $settings,
            'options' => [
                'callback_window_hours' => self::CALLBACK_WINDOW_OPTIONS,
                'date_formate' => self::DATE_FORMAT_OPTIONS,
            ],
        ]);
    }

    public function update(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->role === 'organization') {
            $userOrgId = $user->organization_id ?? optional($user->organization)->id;
            if ((int) $userOrgId !== (int) $organization->id) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'callback_window_hours' => 'required|integer|in:' . implode(',', self::CALLBACK_WINDOW_OPTIONS),
            'date_formate' => 'required|string|in:' . implode(',', self::DATE_FORMAT_OPTIONS),
            'enable_manager_role' => 'required|boolean',
            'enable_working_hours' => 'required|boolean',
            'working_hours' => 'nullable|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $settings = OrganizationSetting::firstOrCreate(['organization_id' => $organization->id]);

        $settings->update([
            'callback_window_hours' => (int) $request->callback_window_hours,
            'date_formate' => $request->date_formate,
            'enable_manager_role' => (bool) $request->enable_manager_role,
            'enable_working_hours' => (bool) $request->enable_working_hours,
            'working_hours' => $request->working_hours !== null ? (int) $request->working_hours : null,
        ]);

        return response()->json([
            'message' => 'Organization settings updated successfully',
            'settings' => $settings->fresh(),
        ]);
    }
}
