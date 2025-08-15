<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'hotspot_user_id',
        'start_time',
        'stop_time',
        'session_time',
        'upload_mb',
        'download_mb',
        'ip_address',
        'mac_address',
        'interface',
        'mikrotik_session_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'stop_time' => 'datetime',
            'session_time' => 'integer',
            'upload_mb' => 'integer',
            'download_mb' => 'integer',
        ];
    }

    /**
     * Get the hotspot user for this session
     */
    public function hotspotUser(): BelongsTo
    {
        return $this->belongsTo(HotspotUser::class);
    }
}
