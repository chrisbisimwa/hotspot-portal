<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserProfileResource;
use App\Http\Responses\ApiResponse;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    use ApiResponse;

    /**
     * Get list of active user profiles (public)
     */
    public function index(): JsonResponse
    {
        $profiles = UserProfile::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return $this->success(
            UserProfileResource::collection($profiles),
            ['total' => $profiles->count()]
        );
    }
}