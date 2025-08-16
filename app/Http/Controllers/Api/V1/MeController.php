<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    use ApiResponse;

    /**
     * Get current authenticated user information
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return $this->success(new UserResource($user));
    }
}