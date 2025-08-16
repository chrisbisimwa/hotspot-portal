<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('health live endpoint returns ok status', function () {
    $response = $this->get('/health/live');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'timestamp'
        ])
        ->assertJson(['status' => 'ok']);
});

test('health ready endpoint checks critical services', function () {
    $response = $this->get('/health/ready');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'checks' => [
                'database' => [
                    'healthy',
                    'message',
                    'duration_ms'
                ],
                'queue' => [
                    'healthy',
                    'message',
                    'duration_ms',
                    'metrics'
                ]
            ],
            'timestamp'
        ])
        ->assertJson(['status' => 'ready']);
});

test('health summary endpoint provides overview', function () {
    $response = $this->get('/health/summary');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'overall_status',
            'healthy_checks',
            'total_checks',
            'timestamp'
        ]);
});

test('metrics endpoint requires valid authorization', function () {
    // Without authorization
    $response = $this->get('/internal/metrics');
    $response->assertStatus(401);
    
    // With invalid token
    $response = $this->withHeaders(['Authorization' => 'Bearer invalid'])
                   ->get('/internal/metrics');
    $response->assertStatus(401);
});

test('metrics endpoint returns prometheus format with valid token', function () {
    config(['app.env' => 'testing']);
    config(['app.internal_metrics_token' => 'test_token']);
    
    // Add to environment
    $_ENV['INTERNAL_METRICS_TOKEN'] = 'test_token';
    
    $response = $this->withHeaders(['Authorization' => 'Bearer test_token'])
                   ->get('/internal/metrics');
    
    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
    
    $content = $response->getContent();
    expect($content)->toContain('# HELP')
        ->and($content)->toContain('# TYPE')
        ->and($content)->toContain('hotspot_total_users');
});

test('correlation id is added to all responses', function () {
    $response = $this->get('/health/live');
    
    $response->assertHeader('X-Request-Id');
    
    $correlationId = $response->headers->get('X-Request-Id');
    expect($correlationId)->toMatch('/^[0-9a-f-]{36}$/'); // UUID format
});

test('security headers are applied', function () {
    $response = $this->get('/health/live');
    
    $response->assertHeader('Content-Security-Policy')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-XSS-Protection', '1; mode=block');
});