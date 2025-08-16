<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Handle user login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        
        // Find user by email or phone
        $user = User::where('email', $credentials['identifier'])
            ->orWhere('phone', $credentials['identifier'])
            ->first();
            
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return $this->error('Invalid credentials', 401, null, [
                'code' => 'INVALID_CREDENTIALS'
            ]);
        }
        
        // Check if user is active
        if ($user->status !== 'active') {
            return $this->error('Account is not active', 403, null, [
                'code' => 'ACCOUNT_INACTIVE'
            ]);
        }
        
        // Update last login
        $user->update(['last_login_at' => now()]);
        
        // Create token with abilities based on role
        $abilities = ['user']; // default
        if ($user->hasRole('admin')) {
            $abilities = ['admin', 'user'];
        } elseif ($user->hasRole('agent')) {
            $abilities = ['agent', 'user'];
        }
        
        $token = $user->createToken('api-token', $abilities)->plainTextToken;
        
        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->user_type,
                'status' => $user->status,
                'roles' => $user->getRoleNames()->toArray(),
            ]
        ], [
            'expires_in' => config('sanctum.expiration') ? config('sanctum.expiration') * 60 : null
        ]);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        
        return $this->success(null, [
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh token (placeholder - TODO implementation)
     */
    public function refresh(Request $request): JsonResponse
    {
        // TODO: Implement token refresh logic
        return $this->error('Token refresh not implemented yet', 501, null, [
            'code' => 'NOT_IMPLEMENTED'
        ]);
    }
}