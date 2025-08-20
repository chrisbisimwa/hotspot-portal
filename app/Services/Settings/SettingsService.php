<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class SettingsService
{
    private string $cacheKey = 'app_settings_cache_v1';

    public function all(): array
    {
        return Cache::remember($this->cacheKey, 300, function () {
            return Setting::query()
                ->get()
                ->keyBy('key')
                ->map(fn(Setting $s) => $s->scalar_value)
                ->toArray();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    public function group(string $group): array
    {
        return Setting::where('group', $group)->get()
            ->keyBy('key')
            ->map->scalar_value
            ->toArray();
    }

    public function update(string $key, mixed $value): bool
    {
        $setting = Setting::where('key', $key)->first();
        if (!$setting) {
            return false;
        }
        $setting->setRawValue($this->coerce($value, $setting->type));
        $setting->updated_by = Auth::id();
        $setting->save();

        $this->invalidateCache();
        event(new \App\Events\Settings\SettingUpdated($setting));

        return true;
    }

    public function bulkUpdate(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->update($key, $value);
        }
    }

    private function coerce(mixed $value, string $type): mixed
    {
        return match($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json','array' => $value,
            default => (string) $value,
        };
    }

    public function invalidateCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}