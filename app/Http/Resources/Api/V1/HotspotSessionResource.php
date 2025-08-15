<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotspotSessionResource extends JsonResource
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
            'hotspot_user_id' => $this->hotspot_user_id,
            'username' => $this->username,
            'session_id' => $this->session_id,
            'nas_ip_address' => $this->nas_ip_address,
            'nas_port_id' => $this->nas_port_id,
            'framed_ip_address' => $this->framed_ip_address,
            'calling_station_id' => $this->calling_station_id,
            'called_station_id' => $this->called_station_id,
            'acct_session_time' => $this->acct_session_time,
            'acct_input_octets' => $this->acct_input_octets,
            'acct_output_octets' => $this->acct_output_octets,
            'start_time' => $this->start_time?->toISOString(),
            'stop_time' => $this->stop_time?->toISOString(),
            'terminate_cause' => $this->terminate_cause,
            'is_active' => $this->stop_time === null,
            'hotspot_user' => $this->whenLoaded('hotspotUser', fn () => new HotspotUserResource($this->hotspotUser)),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}