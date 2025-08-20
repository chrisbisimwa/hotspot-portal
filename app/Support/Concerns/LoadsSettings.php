<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use App\Services\Settings\SettingsService;

trait LoadsSettings
{
    protected function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsService::class)->get($key, $default);
    }
}