<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_key',
        'value',
        'captured_at',
        'meta',
    ];

    protected $casts = [
        'value' => 'float',
        'captured_at' => 'datetime',
        'meta' => 'array',
    ];

    public function scopeForKey($query, string $metricKey)
    {
        return $query->where('metric_key', $metricKey);
    }

    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('captured_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeBetween($query, \DateTimeInterface $start, \DateTimeInterface $end)
    {
        return $query->whereBetween('captured_at', [$start, $end]);
    }
}