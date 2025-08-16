<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'provider' => $this->provider,
            'status' => $this->status,
            'transaction_ref' => $this->transaction_ref,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'net_amount' => $this->net_amount,
            'gateway_amount' => $this->gateway_amount,
            'gateway_currency' => $this->gateway_currency,
            'paid_at' => $this->paid_at?->toISOString(),
            'confirmed_at' => $this->confirmed_at?->toISOString(),
            'refunded_at' => $this->refunded_at?->toISOString(),
            'verified_at' => $this->verified_at?->toISOString(),
            'order' => $this->whenLoaded('order', fn () => new OrderResource($this->order)),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}