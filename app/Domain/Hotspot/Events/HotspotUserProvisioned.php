<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\Events;

use App\Models\HotspotUser;

class HotspotUserProvisioned
{
    public function __construct(
        public readonly HotspotUser $hotspotUser
    ) {
    }
}