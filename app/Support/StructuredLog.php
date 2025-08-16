<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Log;

class StructuredLog
{
    public static function info(string $event, array $context = []): void
    {
        Log::info("[event={$event}]", $context);
    }

    public static function warning(string $event, array $context = []): void
    {
        Log::warning("[event={$event}]", $context);
    }

    public static function error(string $event, array $context = []): void
    {
        Log::error("[event={$event}]", $context);
    }

    public static function debug(string $event, array $context = []): void
    {
        Log::debug("[event={$event}]", $context);
    }

    public static function critical(string $event, array $context = []): void
    {
        Log::critical("[event={$event}]", $context);
    }

    public static function alert(string $event, array $context = []): void
    {
        Log::alert("[event={$event}]", $context);
    }

    public static function emergency(string $event, array $context = []): void
    {
        Log::emergency("[event={$event}]", $context);
    }

    public static function notice(string $event, array $context = []): void
    {
        Log::notice("[event={$event}]", $context);
    }
}