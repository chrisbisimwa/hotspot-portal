<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $order = Order::factory()->create();

        return [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'provider' => fake()->randomElement(['serdipay', 'cash']),
            'status' => PaymentStatus::PENDING->value,
            'transaction_ref' => 'TX'.strtoupper(Str::random(10)),
            'internal_ref' => null,
            'amount' => $order->total_amount,
            'currency' => 'CDF',
            'fee_amount' => 0,
            'net_amount' => $order->total_amount,
            'paid_at' => null,
            'confirmed_at' => null,
            'refunded_at' => null,
            'raw_request' => null,
            'raw_response' => null,
            'callback_payload' => null,
            'meta' => null,
        ];
    }

    /**
     * Configure the factory to use an existing order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'amount' => $order->total_amount,
            'net_amount' => $order->total_amount,
        ]);
    }
}
