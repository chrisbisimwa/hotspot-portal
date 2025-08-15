<?php

declare(strict_types=1);

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Contracts\PaymentGatewayInterface;
use App\Domain\Billing\DTO\CallbackParseResult;
use App\Domain\Billing\DTO\InitiatePaymentResponse;
use App\Domain\Billing\DTO\VerifyPaymentResult;
use App\Domain\Billing\Exceptions\PaymentGatewayException;
use App\Enums\PaymentStatus;
use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SerdiPayGateway implements PaymentGatewayInterface
{
    private Client $httpClient;
    private array $config;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->config = config('payment.serdipay');
    }

    public function initiatePayment(Order $order): InitiatePaymentResponse
    {
        try {
            if ($this->isFakeMode()) {
                return $this->initiatePaymentFake($order);
            }

            $payload = [
                'amount' => $order->total_amount,
                'currency' => 'CDF',
                'order_id' => $order->id,
                'description' => "Order #{$order->id} - Hotspot Package",
                'callback_url' => url('/api/payments/serdipay/callback'),
                'return_url' => url('/payments/success'),
                'cancel_url' => url('/payments/cancel'),
            ];

            $response = $this->httpClient->post($this->config['base_url'] . '/payments/initiate', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['public_key'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::info('SerdiPay: Payment initiated successfully', [
                'order_id' => $order->id,
                'reference' => $data['reference'] ?? null
            ]);

            return new InitiatePaymentResponse(
                reference: $data['reference'],
                redirect_url: $data['redirect_url'] ?? null,
                raw: $data
            );
        } catch (\Exception $e) {
            Log::error('SerdiPay: Failed to initiate payment', [
                'order_id' => $order->id,
                'exception' => $e->getMessage()
            ]);
            throw new PaymentGatewayException('Failed to initiate payment: ' . $e->getMessage());
        }
    }

    public function verifyTransaction(string $transactionRef): VerifyPaymentResult
    {
        try {
            if ($this->isFakeMode()) {
                return $this->verifyTransactionFake($transactionRef);
            }

            $response = $this->httpClient->get($this->config['base_url'] . '/payments/' . $transactionRef, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['secret_key'],
                    'Accept' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            $status = $this->mapSerdiPayStatus($data['status'] ?? 'pending');

            Log::info('SerdiPay: Transaction verified', [
                'reference' => $transactionRef,
                'status' => $status->value
            ]);

            return new VerifyPaymentResult(
                status: $status,
                reference: $transactionRef,
                amount: (float) ($data['amount'] ?? 0),
                currency: $data['currency'] ?? 'CDF',
                raw: $data
            );
        } catch (\Exception $e) {
            Log::error('SerdiPay: Failed to verify transaction', [
                'reference' => $transactionRef,
                'exception' => $e->getMessage()
            ]);
            throw new PaymentGatewayException('Failed to verify transaction: ' . $e->getMessage());
        }
    }

    public function parseCallback(array $payload, array $headers = []): CallbackParseResult
    {
        try {
            $signature = $headers['X-SerdiPay-Signature'] ?? $headers['x-serdipay-signature'] ?? '';
            $signatureValid = $this->verifySignature($payload, $signature);
            
            $status = $this->mapSerdiPayStatus($payload['status'] ?? 'pending');
            $reference = $payload['reference'] ?? '';
            $amount = (float) ($payload['amount'] ?? 0);

            Log::info('SerdiPay: Callback parsed', [
                'reference' => $reference,
                'status' => $status->value,
                'signature_valid' => $signatureValid
            ]);

            return new CallbackParseResult(
                reference: $reference,
                status: $status,
                amount: $amount,
                raw: $payload,
                signature_valid: $signatureValid
            );
        } catch (\Exception $e) {
            Log::error('SerdiPay: Failed to parse callback', [
                'exception' => $e->getMessage(),
                'payload' => $payload
            ]);
            throw new PaymentGatewayException('Failed to parse callback: ' . $e->getMessage());
        }
    }

    private function isFakeMode(): bool
    {
        return $this->config['fake'] ?? true;
    }

    private function verifySignature(array $payload, string $signature): bool
    {
        if ($this->isFakeMode()) {
            return true; // Always valid in fake mode
        }

        $webhookSecret = $this->config['webhook_secret'] ?? '';
        if (empty($webhookSecret)) {
            Log::warning('SerdiPay: Webhook secret not configured');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', json_encode($payload), $webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }

    private function mapSerdiPayStatus(string $serdiPayStatus): PaymentStatus
    {
        return match (strtolower($serdiPayStatus)) {
            'pending' => PaymentStatus::PENDING,
            'initiated', 'processing' => PaymentStatus::PROCESSING,
            'success', 'completed', 'paid' => PaymentStatus::SUCCESS,
            'failed', 'error' => PaymentStatus::FAILED,
            'cancelled', 'canceled' => PaymentStatus::CANCELLED,
            'refunded' => PaymentStatus::REFUNDED,
            default => PaymentStatus::PENDING,
        };
    }

    private function initiatePaymentFake(Order $order): InitiatePaymentResponse
    {
        $reference = 'SP' . strtoupper(Str::random(12));
        
        Log::info('SerdiPay: Fake mode - payment initiated', [
            'order_id' => $order->id,
            'reference' => $reference
        ]);

        return new InitiatePaymentResponse(
            reference: $reference,
            redirect_url: "https://fake-serdipay.com/pay/{$reference}",
            raw: [
                'reference' => $reference,
                'amount' => $order->total_amount,
                'currency' => 'CDF',
                'status' => 'initiated',
                'fake_mode' => true,
            ]
        );
    }

    private function verifyTransactionFake(string $transactionRef): VerifyPaymentResult
    {
        // Simulate different statuses based on reference ending
        $status = match (true) {
            str_ends_with($transactionRef, 'X') => PaymentStatus::FAILED,
            str_ends_with($transactionRef, 'C') => PaymentStatus::CANCELLED,
            str_ends_with($transactionRef, 'R') => PaymentStatus::REFUNDED,
            default => PaymentStatus::SUCCESS,
        };

        $amount = 1000.0; // Fake amount

        Log::info('SerdiPay: Fake mode - transaction verified', [
            'reference' => $transactionRef,
            'status' => $status->value
        ]);

        return new VerifyPaymentResult(
            status: $status,
            reference: $transactionRef,
            amount: $amount,
            currency: 'CDF',
            raw: [
                'reference' => $transactionRef,
                'amount' => $amount,
                'currency' => 'CDF',
                'status' => $status->value,
                'fake_mode' => true,
            ]
        );
    }
}