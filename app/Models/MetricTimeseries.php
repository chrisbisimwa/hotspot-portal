<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetricTimeseries extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'metric_key',
        'value',
        'meta',
        'captured_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'meta' => 'array',
            'captured_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function scopeBetween($query, string $key, $since, $until = null)
    {
        $until = $until ?? now();
        return $query->where('metric_key', $key)
            ->whereBetween('captured_at', [$since, $until])
            ->orderBy('captured_at');
    }
}