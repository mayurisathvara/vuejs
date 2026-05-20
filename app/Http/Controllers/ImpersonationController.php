<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImpersonationController extends Controller
{
    /**
     * Start an impersonation session for the given organization.
     * Admin-only. Creates a Sanctum token for the org's user account
     * without touching the admin's existing tokens.
     */
    public function start(Request $request, Organization $organization): JsonResponse
    {
        $admin = $request->user();

        $orgUser = $organization->user()->with(['organization', 'team'])->first();

        if (! $orgUser) {
            return response()->json(['message' => 'No user account found for this organization.'], 404);
        }

        if ($organization->status !== 'active') {
            return response()->json(['message' => 'Organization is inactive.'], 403);
        }

        if ($orgUser->status !== 'active') {
            return response()->json(['message' => 'Organization user account is inactive.'], 403);
        }

        // Named token so it can be identified and revoked independently.
        // Does NOT delete the admin's existing token.
        $token = $orgUser->createToken('impersonation:admin-' . $admin->id)->plainTextToken;

        Log::info('Admin impersonation started', [
            'admin_id'    => $admin->id,
            'admin_email' => $admin->email,
            'org_id'      => $organization->id,
            'org_name'    => $organization->name,
            'ip'          => $request->ip(),
        ]);

        return response()->json([
            'user'              => $orgUser,
            'token'             => $token,
            'organization_name' => $organization->name,
            'message'           => 'Impersonation started',
        ]);
    }

    /**
     * Stop the current impersonation session.
     * Called with the impersonation token; revokes only that token.
     */
    public function stop(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        Log::info('Impersonation token revoked', [
            'token_name' => $token->name,
            'user_id'    => $request->user()->id,
            'ip'         => $request->ip(),
        ]);

        $token->delete();

        return response()->json(['message' => 'Impersonation stopped']);
    }
}
