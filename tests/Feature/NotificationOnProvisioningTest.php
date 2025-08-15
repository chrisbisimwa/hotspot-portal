<?php

declare(strict_types=1);

use App\Domain\Hotspot\Events\HotspotUserProvisioned;
use App\Domain\Shared\Services\NotificationService;
use App\Models\HotspotUser;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Event;

test('hotspot user provisioned triggers credentials notification', function () {
    Event::fake();

    // Arrange
    $user = User::factory()->create([
        'phone' => '+243123456789',
        'email' => 'test@example.com',
    ]);
    $profile = UserProfile::factory()->create(['name' => 'Standard Package']);
    $hotspotUser = HotspotUser::factory()->create([
        'username' => 'HS20250815120001',
        'password' => 'testpass123',
        'user_profile_id' => $profile->id,
        'owner_id' => $user->id,
        'validity_minutes' => 60,
    ]);

    // Act - simulate HotspotUserProvisioned event
    event(new HotspotUserProvisioned($hotspotUser));

    // Assert that a notification record was created
    expect(Notification::count())->toBe(1);
    
    $notification = Notification::first();
    expect($notification->channel)->toBe('sms'); // Default channel
    expect($notification->to)->toBe($user->phone);
    expect($notification->message)->toContain('HS20250815120001');
    expect($notification->message)->toContain('testpass123');
    expect($notification->message)->toContain('Standard Package');
    expect($notification->message)->toContain('60 minutes');
});

test('notification service sends sms successfully', function () {
    // Arrange
    $notificationService = app(NotificationService::class);
    
    // Act
    $result = $notificationService->sendSms('+243123456789', 'Test message', ['test' => true]);
    
    // Assert
    expect($result)->toBeTrue();
    expect(Notification::count())->toBe(1);
    
    $notification = Notification::first();
    expect($notification->channel)->toBe('sms');
    expect($notification->to)->toBe('+243123456789');
    expect($notification->message)->toBe('Test message');
    expect($notification->status)->toBe('sent');
    expect($notification->sent_at)->not->toBeNull();
});

test('notification service sends email successfully', function () {
    // Arrange
    $notificationService = app(NotificationService::class);
    
    // Act
    $result = $notificationService->sendEmail(
        'test@example.com',
        'Test Subject',
        'Test message body',
        ['test' => true]
    );
    
    // Assert
    expect($result)->toBeTrue();
    expect(Notification::count())->toBe(1);
    
    $notification = Notification::first();
    expect($notification->channel)->toBe('email');
    expect($notification->to)->toBe('test@example.com');
    expect($notification->subject)->toBe('Test Subject');
    expect($notification->message)->toBe('Test message body');
    expect($notification->status)->toBe('sent');
    expect($notification->sent_at)->not->toBeNull();
});

test('credentials message format is correct', function () {
    // Arrange
    $user = User::factory()->create([
        'phone' => '+243123456789',
    ]);
    $profile = UserProfile::factory()->create(['name' => 'Premium Package']);
    $hotspotUser = HotspotUser::factory()->create([
        'username' => 'HS20250815120001',
        'password' => 'ABC123xyz9',
        'user_profile_id' => $profile->id,
        'owner_id' => $user->id,
        'validity_minutes' => 120,
    ]);

    // Act
    event(new HotspotUserProvisioned($hotspotUser));

    // Assert
    $notification = Notification::first();
    $expectedMessage = "Vos identifiants Hotspot: HS20250815120001 / ABC123xyz9 – Profil: Premium Package – Validité: 120 minutes.";
    expect($notification->message)->toBe($expectedMessage);
});

test('notification uses email fallback when user has no phone', function () {
    // Arrange
    $user = User::factory()->create([
        'phone' => null,
        'email' => 'test@example.com',
    ]);
    $profile = UserProfile::factory()->create();
    $hotspotUser = HotspotUser::factory()->create([
        'user_profile_id' => $profile->id,
        'owner_id' => $user->id,
    ]);

    // Act
    event(new HotspotUserProvisioned($hotspotUser));

    // Assert
    $notification = Notification::first();
    expect($notification->channel)->toBe('sms'); // Still tries SMS channel first
    expect($notification->to)->toBeNull(); // But recipient is null
});

test('whatsapp notification not implemented returns failure', function () {
    // Arrange
    $notificationService = app(NotificationService::class);
    
    // Act
    $result = $notificationService->sendWhatsapp('+243123456789', 'Test message');
    
    // Assert
    expect($result)->toBeFalse();
    expect(Notification::count())->toBe(1);
    
    $notification = Notification::first();
    expect($notification->channel)->toBe('whatsapp');
    expect($notification->status)->toBe('failed');
    expect($notification->error_message)->toBe('WhatsApp channel not implemented');
});