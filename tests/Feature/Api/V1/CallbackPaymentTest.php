<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        \Database\Seeders\RolesAndPermissionsSeeder::class,
    ]);
});

test('serdipay callback updates payment status', function () {
    $user = User::factory()->create(['status' => 'active']);
    
    // Create a payment with initiated status
    $payment = Payment::factory()->create([
        'user_id' => $user->id,
        'status' => 'initiated',
        'transaction_ref' => 'TEST123456',
        'provider' => 'serdipay',
    ]);

    // Simulate callback payload (this would be from SerdiPay)
    $callbackPayload = [
        'reference' => 'TEST123456', // Changed from transaction_id to reference
        'status' => 'SUCCESS',
        'amount' => '1000.00',
        'currency' => 'CDF',
        'signature' => 'fake_signature_for_testing',
    ];

    $response = $this->postJson('/api/v1/payments/callback/serdipay', $callbackPayload);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'received',
                'processed',
                'payment_id',
                'status'
            ]
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'received' => true,
                'processed' => true,
                'payment_id' => $payment->id,
            ]
        ]);

    // Payment should be updated
    $payment->refresh();
    expect($payment->callback_payload)->not->toBeNull();
});

test('callback handles invalid payload gracefully', function () {
    $response = $this->postJson('/api/v1/payments/callback/serdipay', [
        'invalid' => 'payload'
    ]);

    $response->assertStatus(500)
        ->assertJsonStructure([
            'success',
            'data',
            'meta' => [
                'code'
            ],
            'errors',
            'message'
        ])
        ->assertJson([
            'success' => false,
            'meta' => [
                'code' => 'CALLBACK_PROCESSING_FAILED'
            ]
        ]);
});