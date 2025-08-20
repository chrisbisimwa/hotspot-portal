<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Setting extends Model
{
    protected $fillable = [
        'key','group','type','value','meta','updated_by'
    ];

    protected $casts = [
        'value' => 'array',
        'meta' => 'array',
    ];

    public function getScalarValueAttribute()
    {
        $val = $this->value;
        if ($val === null) return null;

        switch ($this->type) {
            case 'int': return (int) $val['raw'];
            case 'float': return (float) $val['raw'];
            case 'bool': return (bool) $val['raw'];
            case 'json':
            case 'array': return $val['raw'];
            case 'string':
            default: return (string) $val['raw'];
        }
    }

    public function setRawValue(mixed $raw): void
    {
        $this->value = ['raw' => $raw];
    }
}