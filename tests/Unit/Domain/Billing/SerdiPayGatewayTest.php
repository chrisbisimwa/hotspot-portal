<?php

declare(strict_types=1);

use App\Domain\Billing\Services\SerdiPayGateway;
use App\Enums\PaymentStatus;
use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    config(['payment.serdipay.fake' => true]);
});

test('initiate payment fake returns reference', function () {
    $httpClient = new Client();
    $gateway = new SerdiPayGateway($httpClient);
    
    $order = Order::factory()->create([
        'total_amount' => 1500.00
    ]);

    $response = $gateway->initiatePayment($order);

    expect($response->reference)->toStartWith('SP')
        ->and($response->redirect_url)->toContain('fake-serdipay.com')
        ->and($response->raw)->toHaveKey('fake_mode')
        ->and($response->raw['fake_mode'])->toBeTrue()
        ->and($response->raw['amount'])->toBe(1500.00);
});

test('verify transaction success simulated', function () {
    $httpClient = new Client();
    $gateway = new SerdiPayGateway($httpClient);

    // Test successful transaction (default case)
    $result = $gateway->verifyTransaction('SP123456789ABC');

    expect($result->status)->toBe(PaymentStatus::SUCCESS)
        ->and($result->reference)->toBe('SP123456789ABC')
        ->and($result->amount)->toBe(1000.0)
        ->and($result->currency)->toBe('CDF')
        ->and($result->raw)->toHaveKey('fake_mode')
        ->and($result->raw['fake_mode'])->toBeTrue();
});

test('verify transaction failed simulation', function () {
    $httpClient = new Client();
    $gateway = new SerdiPayGateway($httpClient);

    // Test failed transaction (ends with X)
    $result = $gateway->verifyTransaction('SP123456789X');

    expect($result->status)->toBe(PaymentStatus::FAILED)
        ->and($result->reference)->toBe('SP123456789X');
});

test('verify transaction cancelled simulation', function () {
    $httpClient = new Client();
    $gateway = new SerdiPayGateway($httpClient);

    // Test cancelled transaction (ends with C)
    $result = $gateway->verifyTransaction('SP123456789C');

    expect($result->status)->toBe(PaymentStatus::CANCELLED)
        ->and($result->reference)->toBe('SP123456789C');
});

test('verify transaction refunded simulation', function () {
    $httpClient = new Client();
    $gateway = new SerdiPayGateway($httpClient);

    // Test refunded transaction (ends with R)
    $result = $gateway->verifyTransaction('SP123456789R');

    expect($result->status)->toBe(PaymentStatus::REFUNDED)
        ->and($result->reference)->toBe('SP123456789R');
});

test('parse callback returns valid result', function () {
    $httpClient = new Client();
    $gateway = new SerdiPayGateway($httpClient);

    $payload = [
        'reference' => 'SP123456789ABC',
        'status' => 'success',
        'amount' => 2500.00,
    ];

    $headers = [
        'X-SerdiPay-Signature' => 'fake-signature'
    ];

    $result = $gateway->parseCallback($payload, $headers);

    expect($result->reference)->toBe('SP123456789ABC')
        ->and($result->status)->toBe(PaymentStatus::SUCCESS)
        ->and($result->amount)->toBe(2500.00)
        ->and($result->signature_valid)->toBeTrue() // Always true in fake mode
        ->and($result->raw)->toBe($payload);
});