<?php

declare(strict_types=1);

use App\Domain\Billing\Contracts\PaymentGatewayInterface;
use App\Domain\Billing\DTO\InitiatePaymentResponse;
use App\Domain\Billing\DTO\VerifyPaymentResult;
use App\Domain\Billing\Exceptions\PaymentGatewayException;
use App\Domain\Billing\Services\PaymentService;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('initiate creates payment and sets initiated', function () {
    $order = Order::factory()->create([
        'total_amount' => 1200.00
    ]);

    $mockGateway = $this->mock(PaymentGatewayInterface::class, function (MockInterface $mock) use ($order) {
        $mock->shouldReceive('initiatePayment')
            ->with($order)
            ->once()
            ->andReturn(new InitiatePaymentResponse(
                reference: 'SP123456789',
                redirect_url: 'https://payment.url',
                raw: ['status' => 'initiated']
            ));
    });

    $service = new PaymentService($mockGateway);

    $payment = $service->initiate($order, 'serdipay');

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->order_id)->toBe($order->id)
        ->and($payment->user_id)->toBe($order->user_id)
        ->and($payment->provider)->toBe('serdipay')
        ->and($payment->status)->toBe(PaymentStatus::INITIATED->value)
        ->and($payment->transaction_ref)->toBe('SP123456789')
        ->and($payment->amount)->toBe(1200.00)
        ->and($payment->raw_response)->toHaveKey('status');
});

test('verify updates payment status', function () {
    $payment = Payment::factory()->create([
        'status' => PaymentStatus::INITIATED->value,
        'transaction_ref' => 'SP123456789',
        'amount' => 1500.00
    ]);

    $mockGateway = $this->mock(PaymentGatewayInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('verifyTransaction')
            ->with('SP123456789')
            ->once()
            ->andReturn(new VerifyPaymentResult(
                status: PaymentStatus::SUCCESS,
                reference: 'SP123456789',
                amount: 1500.00,
                currency: 'CDF',
                raw: ['verified' => true]
            ));
    });

    $service = new PaymentService($mockGateway);

    $updatedPayment = $service->verify($payment);

    expect($updatedPayment->status)->toBe(PaymentStatus::SUCCESS->value)
        ->and($updatedPayment->paid_at)->not->toBeNull()
        ->and($updatedPayment->confirmed_at)->not->toBeNull()
        ->and($updatedPayment->meta)->toHaveKey('verified_at');
});

test('apply status with valid transition', function () {
    $payment = Payment::factory()->create([
        'status' => PaymentStatus::PENDING->value
    ]);

    $mockGateway = $this->mock(PaymentGatewayInterface::class);
    $service = new PaymentService($mockGateway);

    $updatedPayment = $service->applyStatus($payment, PaymentStatus::INITIATED);

    expect($updatedPayment->status)->toBe(PaymentStatus::INITIATED->value);
});

test('invalid transition throws exception', function () {
    $payment = Payment::factory()->create([
        'status' => PaymentStatus::SUCCESS->value
    ]);

    $mockGateway = $this->mock(PaymentGatewayInterface::class);
    $service = new PaymentService($mockGateway);

    expect(fn() => $service->applyStatus($payment, PaymentStatus::PENDING))
        ->toThrow(PaymentGatewayException::class, 'Invalid status transition');
});

test('payment model can transition to method works correctly', function () {
    $payment = new Payment(['status' => PaymentStatus::PENDING->value]);
    
    expect($payment->canTransitionTo(PaymentStatus::INITIATED))->toBeTrue()
        ->and($payment->canTransitionTo(PaymentStatus::CANCELLED))->toBeTrue()
        ->and($payment->canTransitionTo(PaymentStatus::SUCCESS))->toBeFalse();

    $payment->status = PaymentStatus::SUCCESS->value;
    expect($payment->canTransitionTo(PaymentStatus::REFUNDED))->toBeTrue()
        ->and($payment->canTransitionTo(PaymentStatus::PENDING))->toBeFalse();

    $payment->status = PaymentStatus::FAILED->value;
    expect($payment->canTransitionTo(PaymentStatus::SUCCESS))->toBeFalse()
        ->and($payment->canTransitionTo(PaymentStatus::REFUNDED))->toBeFalse();
});