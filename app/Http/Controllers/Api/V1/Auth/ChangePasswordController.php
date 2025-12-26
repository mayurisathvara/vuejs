<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    use ApiResponse;
    /**
     * Handle change password request for authenticated mobile app user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'confirmed', Password::min(8)],
            'new_password_confirmation' => 'required|string',
        ]);

        $user = $request->user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password is incorrect.', 401);
        }

        // Check if new password is same as current password
        if (Hash::check($request->new_password, $user->password)) {
            return $this->errorResponse('New password cannot be the same as current password.', 422);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Optionally revoke all tokens to force re-login
        $user->tokens()->delete();

        return $this->successResponse(null, 'Password changed successfully. Please login again with your new password.');
    }
}
