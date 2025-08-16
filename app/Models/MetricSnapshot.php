<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetricSnapshot extends Model
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'snapshot_date',
        'metric_key',
        'value',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'value' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Scope snapshots by date
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('snapshot_date', $date);
    }

    /**
     * Scope snapshots by metric key
     */
    public function scopeForMetric($query, $metricKey)
    {
        return $query->where('metric_key', $metricKey);
    }

    /**
     * Get snapshots for a date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('snapshot_date', [$startDate, $endDate]);
    }
}