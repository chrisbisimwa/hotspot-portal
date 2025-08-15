<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'mikrotik_profile',
        'price',
        'validity_minutes',
        'data_limit_mb',
        'description',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'validity_minutes' => 'integer',
            'data_limit_mb' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get hotspot users for this profile
     */
    public function hotspotUsers(): HasMany
    {
        return $this->hasMany(HotspotUser::class);
    }

    /**
     * Get orders for this profile
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
