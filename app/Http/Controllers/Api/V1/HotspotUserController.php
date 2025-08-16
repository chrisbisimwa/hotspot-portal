<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\HotspotUserResource;
use App\Http\Resources\Api\V1\HotspotSessionResource;
use App\Http\Responses\ApiResponse;
use App\Models\HotspotUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HotspotUserController extends Controller
{
    use ApiResponse;

    /**
     * Get user's hotspot users with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', config('api.default_page_size')), config('api.max_page_size'));
        
        $hotspotUsers = HotspotUser::with(['userProfile'])
            ->where('owner_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
        return $this->success(
            HotspotUserResource::collection($hotspotUsers),
            [
                'pagination' => [
                    'current_page' => $hotspotUsers->currentPage(),
                    'per_page' => $hotspotUsers->perPage(),
                    'total' => $hotspotUsers->total(),
                    'last_page' => $hotspotUsers->lastPage(),
                ]
            ]
        );
    }

    /**
     * Get a specific hotspot user
     */
    public function show(Request $request, HotspotUser $hotspotUser): JsonResponse
    {
        // Check ownership
        if ($hotspotUser->owner_id !== $request->user()->id) {
            return $this->error('Hotspot user not found', 404, null, [
                'code' => 'HOTSPOT_USER_NOT_FOUND'
            ]);
        }
        
        return $this->success(new HotspotUserResource($hotspotUser->load('userProfile')));
    }

    /**
     * Get sessions for a specific hotspot user
     */
    public function sessions(Request $request, HotspotUser $hotspotUser): JsonResponse
    {
        // Check ownership
        if ($hotspotUser->owner_id !== $request->user()->id) {
            return $this->error('Hotspot user not found', 404, null, [
                'code' => 'HOTSPOT_USER_NOT_FOUND'
            ]);
        }
        
        $perPage = min($request->get('per_page', config('api.default_page_size')), config('api.max_page_size'));
        
        $sessions = $hotspotUser->sessions()
            ->orderBy('start_time', 'desc')
            ->paginate($perPage);
            
        return $this->success(
            HotspotSessionResource::collection($sessions),
            [
                'pagination' => [
                    'current_page' => $sessions->currentPage(),
                    'per_page' => $sessions->perPage(),
                    'total' => $sessions->total(),
                    'last_page' => $sessions->lastPage(),
                ]
            ]
        );
    }
}