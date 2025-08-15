<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\HotspotSessionResource;
use App\Http\Responses\ApiResponse;
use App\Models\HotspotSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    use ApiResponse;

    /**
     * Get all sessions for user's hotspot users
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', config('api.default_page_size')), config('api.max_page_size'));
        
        // Get sessions for all hotspot users owned by the current user
        $sessions = HotspotSession::with(['hotspotUser'])
            ->whereHas('hotspotUser', function ($query) use ($request) {
                $query->where('owner_id', $request->user()->id);
            })
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