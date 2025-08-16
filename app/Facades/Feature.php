<?php

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\Feature\FeatureService;

/**
 * @method static bool enabled(string $key)
 * @method static \App\Models\FeatureFlag enable(string $key, array $meta = [])
 * @method static \App\Models\FeatureFlag disable(string $key)
 * @method static array all()
 * @method static array getAllEnabled()
 * @method static void setBulk(array $flags)
 */
class Feature extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FeatureService::class;
    }
}