<?php

declare(strict_types=1);

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Contracts\PaymentGatewayInterface;
use App\Domain\Billing\Events\PaymentSucceeded;
use App\Domain\Billing\Exceptions\PaymentGatewayException;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    private PaymentGatewayInterface $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Initiate a payment for an order
     */
    public function initiate(Order $order, string $provider = 'serdipay'): Payment
    {
        try {
            // Create payment record with pending status
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'provider' => $provider,
                'status' => PaymentStatus::PENDING->value,
                'transaction_ref' => 'TEMP_' . strtoupper(Str::random(10)),
                'amount' => $order->total_amount,
                'currency' => 'CDF',
                'net_amount' => $order->total_amount,
            ]);

            // Call gateway to initiate payment
            $response = $this->gateway->initiatePayment($order);

            // Update payment with gateway response
            $payment->update([
                'status' => PaymentStatus::INITIATED->value,
                'transaction_ref' => $response->reference,
                'raw_response' => $response->raw,
            ]);

            Log::info('Payment initiated successfully', [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'reference' => $response->reference
            ]);

            return $payment->fresh();
        } catch (\Exception $e) {
            Log::error('Failed to initiate payment', [
                'order_id' => $order->id,
                'provider' => $provider,
                'exception' => $e->getMessage()
            ]);
            throw new PaymentGatewayException('Failed to initiate payment: ' . $e->getMessage());
        }
    }

    /**
     * Verify a payment status with the gateway
     */
    public function verify(Payment $payment): Payment
    {
        try {
            $result = $this->gateway->verifyTransaction($payment->transaction_ref);
            
            $payment = $this->applyStatus($payment, $result->status, [
                'verified_at' => now(),
                'gateway_amount' => $result->amount,
                'gateway_currency' => $result->currency,
                'verification_raw' => $result->raw,
            ]);

            Log::info('Payment verified', [
                'payment_id' => $payment->id,
                'reference' => $payment->transaction_ref,
                'status' => $result->status->value
            ]);

            return $payment;
        } catch (\Exception $e) {
            Log::error('Failed to verify payment', [
                'payment_id' => $payment->id,
                'reference' => $payment->transaction_ref,
                'exception' => $e->getMessage()
            ]);
            throw new PaymentGatewayException('Failed to verify payment: ' . $e->getMessage());
        }
    }

    /**
     * Apply status transition to a payment
     */
    public function applyStatus(Payment $payment, PaymentStatus $status, array $meta = []): Payment
    {
        // Check if transition is valid
        if (!$payment->canTransitionTo($status)) {
            throw new PaymentGatewayException(
                "Invalid status transition from {$payment->status} to {$status->value}"
            );
        }

        $updateData = [
            'status' => $status->value,
            'meta' => array_merge($payment->meta ?? [], $meta),
        ];

        // Set timestamps based on status
        switch ($status) {
            case PaymentStatus::SUCCESS:
                $updateData['paid_at'] = now();
                $updateData['confirmed_at'] = now();
                // TODO: Trigger PaymentSucceeded event here (next step)
                break;
            case PaymentStatus::FAILED:
            case PaymentStatus::CANCELLED:
                // Clear any success timestamps
                break;
            case PaymentStatus::REFUNDED:
                $updateData['refunded_at'] = now();
                break;
        }

        $payment->update($updateData);

        // Dispatch events based on status
        if ($status === PaymentStatus::SUCCESS) {
            event(new PaymentSucceeded($payment));
        }
        // TODO: Add PaymentFailed event dispatch when status is FAILED

        Log::info('Payment status updated', [
            'payment_id' => $payment->id,
            'old_status' => $payment->getOriginal('status'),
            'new_status' => $status->value
        ]);

        return $payment->fresh();
    }

    /**
     * Record and process a payment callback
     */
    public function recordCallback(array $payload, array $headers = []): Payment
    {
        try {
            $result = $this->gateway->parseCallback($payload, $headers);
            
            // Find payment by transaction reference
            $payment = Payment::where('transaction_ref', $result->reference)->firstOrFail();
            
            // Store callback payload
            $payment->update([
                'callback_payload' => $payload,
            ]);

            // Apply status if signature is valid
            if ($result->signature_valid) {
                $payment = $this->applyStatus($payment, $result->status, [
                    'callback_processed_at' => now(),
                    'callback_amount' => $result->amount,
                    'signature_verified' => true,
                ]);
            } else {
                Log::warning('Payment callback received with invalid signature', [
                    'payment_id' => $payment->id,
                    'reference' => $result->reference
                ]);
            }

            Log::info('Payment callback recorded', [
                'payment_id' => $payment->id,
                'reference' => $result->reference,
                'status' => $result->status->value,
                'signature_valid' => $result->signature_valid
            ]);

            return $payment;
        } catch (\Exception $e) {
            Log::error('Failed to record payment callback', [
                'payload' => $payload,
                'exception' => $e->getMessage()
            ]);
            throw new PaymentGatewayException('Failed to record payment callback: ' . $e->getMessage());
        }
    }
}