<?php

declare(strict_types=1);

namespace App\Domain\Billing\Contracts;

use App\Domain\Billing\DTO\CallbackParseResult;
use App\Domain\Billing\DTO\InitiatePaymentResponse;
use App\Domain\Billing\DTO\VerifyPaymentResult;
use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment for an order
     */
    public function initiatePayment(Order $order): InitiatePaymentResponse;

    /**
     * Verify a transaction status
     */
    public function verifyTransaction(string $transactionRef): VerifyPaymentResult;

    /**
     * Parse webhook callback payload
     */
    public function parseCallback(array $payload, array $headers = []): CallbackParseResult;
}