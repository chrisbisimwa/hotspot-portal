<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Http\Responses\ApiResponse;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Get user's notifications with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', config('api.default_page_size')), config('api.max_page_size'));
        
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
        return $this->success(
            NotificationResource::collection($notifications),
            [
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'last_page' => $notifications->lastPage(),
                ]
            ]
        );
    }

    /**
     * Get a specific notification
     */
    public function show(Request $request, Notification $notification): JsonResponse
    {
        // Check ownership
        if ($notification->user_id !== $request->user()->id) {
            return $this->error('Notification not found', 404, null, [
                'code' => 'NOTIFICATION_NOT_FOUND'
            ]);
        }
        
        return $this->success(new NotificationResource($notification));
    }
}