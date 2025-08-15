<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotspotUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password, // Show password for user's own hotspot accounts
            'owner_id' => $this->owner_id,
            'status' => $this->status,
            'validity_minutes' => $this->validity_minutes,
            'max_concurrent_sessions' => $this->max_concurrent_sessions,
            'download_speed_kbps' => $this->download_speed_kbps,
            'upload_speed_kbps' => $this->upload_speed_kbps,
            'expires_at' => $this->expires_at?->toISOString(),
            'mikrotik_created_at' => $this->mikrotik_created_at?->toISOString(),
            'mikrotik_disabled_at' => $this->mikrotik_disabled_at?->toISOString(),
            'user_profile' => $this->whenLoaded('userProfile', fn () => new UserProfileResource($this->userProfile)),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}