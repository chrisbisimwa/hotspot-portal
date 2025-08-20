<?php

declare(strict_types=1);

namespace App\Services\Hotspot;

use App\Models\HotspotUser;
use App\Models\UserProfile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BatchHotspotUserService
{
    public function createBatch(
        int $ownerId,
        int $profileId,
        int $quantity,
        ?int $overrideValidity = null,
        ?int $overrideQuotaMb = null,
        ?string $usernamePrefix = 'HS',
        int $passwordLength = 8,
        ?string $batchRef = null
    ): array {
        $profile = UserProfile::findOrFail($profileId);

        $batchRef = $batchRef ?: Str::upper(Str::uuid()->toString());

        $users = [];

        DB::transaction(function () use (
            &$users,
            $ownerId,
            $profile,
            $quantity,
            $overrideValidity,
            $overrideQuotaMb,
            $usernamePrefix,
            $passwordLength,
            $batchRef
        ) {
            for ($i = 1; $i <= $quantity; $i++) {
                $username = $this->generateUsername($usernamePrefix, $i);
                $password =  $username; // Use the username as password for simplicity

                $users[] = HotspotUser::create([
                    'username' => $username,
                    'password' => $password,
                    'user_profile_id' => $profile->id,
                    'owner_id' => $ownerId,
                    'status' => 'active',
                    'validity_minutes' => $overrideValidity ?? $profile->validity_minutes,
                    'data_limit_mb' => $overrideQuotaMb ?? $profile->data_limit_mb,
                    'batch_ref' => $batchRef,
                ]);
            }
        });

        return [
            'batch_ref' => $batchRef,
            'users' => $users,
            'profile' => $profile,
        ];
    }

    private function generateUsername(string $prefix, int $index): string
    {
        
        $code=strtoupper(Str::random(6));
        return sprintf('%s%s', $prefix, $code);
    }
}