<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'status',
        'severity',
        'started_at',
        'detected_at',
        'mitigated_at',
        'resolved_at',
        'closed_at',
        'detection_source',
        'summary',
        'root_cause',
        'impact',
        'meta',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => IncidentStatus::class,
        'severity' => IncidentSeverity::class,
        'started_at' => 'datetime',
        'detected_at' => 'datetime',
        'mitigated_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Incident $incident) {
            if (empty($incident->slug)) {
                $incident->slug = Str::slug($incident->title) . '-' . Str::random(8);
            }
        });
    }

    public function updates(): HasMany
    {
        return $this->hasMany(IncidentUpdate::class)->orderBy('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isOpen(): bool
    {
        return $this->status === IncidentStatus::OPEN;
    }

    public function isResolved(): bool
    {
        return $this->status === IncidentStatus::RESOLVED;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [IncidentStatus::RESOLVED, IncidentStatus::FALSE_POSITIVE]);
    }
}