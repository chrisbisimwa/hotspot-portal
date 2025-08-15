<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('user factory creates user with default state', function () {
    $user = User::factory()->create();

    expect($user->user_type)->toBe(UserType::USER->value)
        ->and($user->status)->toBe(UserStatus::ACTIVE->value)
        ->and($user->name)->not->toBeEmpty()
        ->and($user->email)->not->toBeEmpty()
        ->and($user->phone)->not->toBeEmpty();
});

test('user factory admin state', function () {
    $user = User::factory()->admin()->create();

    expect($user->user_type)->toBe(UserType::ADMIN->value)
        ->and($user->status)->toBe(UserStatus::ACTIVE->value);
});

test('user factory agent state', function () {
    $user = User::factory()->agent()->create();

    expect($user->user_type)->toBe(UserType::AGENT->value)
        ->and($user->status)->toBe(UserStatus::ACTIVE->value);
});

test('user factory inactive state', function () {
    $user = User::factory()->inactive()->create();

    expect($user->status)->toBe(UserStatus::INACTIVE->value);
});

test('user factory banned state', function () {
    $user = User::factory()->banned()->create();

    expect($user->status)->toBe(UserStatus::BANNED->value);
});
