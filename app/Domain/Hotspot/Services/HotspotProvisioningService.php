<?php

declare(strict_types=1);

namespace App\Domain\Hotspot\Services;

use App\Domain\Hotspot\Contracts\MikrotikApiInterface;
use App\Domain\Hotspot\DTO\HotspotUserProvisionData;
use App\Domain\Hotspot\Events\HotspotUserProvisioned;
use App\Domain\Hotspot\Events\OrderCompleted;
use App\Domain\Hotspot\Exceptions\ProvisioningException;
use App\Enums\OrderStatus;
use App\Models\HotspotUser;
use App\Models\Order;
use App\Models\UserProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HotspotProvisioningService
{
    public function __construct(
        private MikrotikApiInterface $mikrotikApi
    ) {
    }

    /**
     * Provision an order and create hotspot users based on quantity
     *
     * @param Order $order
     * @return Collection<HotspotUser>
     * @throws ProvisioningException
     */
    public function provisionOrder(Order $order): Collection
    {
        // Check if order status is payment_received
        if ($order->status !== OrderStatus::PAYMENT_RECEIVED->value) {
            throw new ProvisioningException(
                "Cannot provision order #{$order->id}: status must be 'payment_received', current status is '{$order->status}'"
            );
        }

        // Update order status to processing
        $order->update(['status' => OrderStatus::PROCESSING->value]);

        Log::info('Starting hotspot provisioning', [
            'order_id' => $order->id,
            'quantity' => $order->quantity,
            'user_profile_id' => $order->user_profile_id
        ]);

        $provisionedUsers = collect();
        $hasFailures = false;

        // Load the user profile
        $profile = $order->userProfile;

        try {
            // Provision each user based on quantity
            for ($i = 1; $i <= $order->quantity; $i++) {
                try {
                    $hotspotUser = $this->provisionSingle($order, $profile, $i);
                    $provisionedUsers->push($hotspotUser);
                    
                    // Dispatch event for each provisioned user
                    event(new HotspotUserProvisioned($hotspotUser));
                    
                    Log::info('Hotspot user provisioned successfully', [
                        'order_id' => $order->id,
                        'hotspot_user_id' => $hotspotUser->id,
                        'username' => $hotspotUser->username,
                        'index' => $i
                    ]);
                } catch (\Exception $e) {
                    $hasFailures = true;
                    Log::error('Failed to provision hotspot user', [
                        'order_id' => $order->id,
                        'index' => $i,
                        'exception' => $e->getMessage()
                    ]);
                }
            }

            // Update order status based on results
            if ($provisionedUsers->isEmpty()) {
                // Complete failure
                $order->update([
                    'status' => OrderStatus::CANCELLED->value,
                    'meta' => array_merge($order->meta ?? [], ['provisioning_error' => 'All users failed to provision'])
                ]);
                
                throw new ProvisioningException("Failed to provision any users for order #{$order->id}");
            } else {
                // Success (partial or complete)
                $updateData = [
                    'status' => OrderStatus::COMPLETED->value,
                    'completed_at' => now()
                ];

                if ($hasFailures) {
                    $updateData['meta'] = array_merge($order->meta ?? [], ['partial_failure' => true]);
                }

                $order->update($updateData);

                // Dispatch order completed event if at least one user was provisioned
                event(new OrderCompleted($order));

                Log::info('Order provisioning completed', [
                    'order_id' => $order->id,
                    'provisioned_count' => $provisionedUsers->count(),
                    'has_failures' => $hasFailures
                ]);
            }

            return $provisionedUsers;
        } catch (ProvisioningException $e) {
            throw $e;
        } catch (\Exception $e) {
            $order->update([
                'status' => OrderStatus::CANCELLED->value,
                'meta' => array_merge($order->meta ?? [], ['provisioning_error' => $e->getMessage()])
            ]);
            
            throw new ProvisioningException("Provisioning failed for order #{$order->id}: " . $e->getMessage());
        }
    }

    /**
     * Provision a single hotspot user for an order
     *
     * @param Order $order
     * @param UserProfile $profile
     * @param int|null $index
     * @return HotspotUser
     * @throws \Exception
     */
    public function provisionSingle(Order $order, UserProfile $profile, ?int $index = null): HotspotUser
    {
        $username = $this->generateUsername($index);
        $password = $this->generatePassword();

        // Create the hotspot user record
        $hotspotUser = HotspotUser::create([
            'username' => $username,
            'password' => $password,
            'user_profile_id' => $profile->id,
            'owner_id' => $order->user_id,
            'validity_minutes' => $profile->validity_minutes,
            'data_limit_mb' => $profile->data_limit_mb,
            'expired_at' => now()->addMinutes($profile->validity_minutes),
            'status' => 'active',
        ]);

        try {
            // Create user in Mikrotik
            $provisionData = new HotspotUserProvisionData(
                username: $username,
                password: $password,
                profileName: $profile->mikrotik_profile,
                validityMinutes: $profile->validity_minutes,
                dataLimitMb: $profile->data_limit_mb
            );

            $result = $this->mikrotikApi->createUser($provisionData);
            
            // Update hotspot user with Mikrotik ID
            $hotspotUser->update(['mikrotik_id' => $result->mikrotikId]);

            Log::info('Mikrotik user created successfully', [
                'username' => $username,
                'mikrotik_id' => $result->mikrotikId,
                'hotspot_user_id' => $hotspotUser->id
            ]);

            return $hotspotUser;
        } catch (\Exception $e) {
            Log::warning('Mikrotik API call failed during provisioning', [
                'username' => $username,
                'hotspot_user_id' => $hotspotUser->id,
                'exception' => $e->getMessage()
            ]);

            // Mark the hotspot user as having failed Mikrotik provisioning
            $hotspotUser->update([
                'status' => 'failed'
            ]);

            throw $e;
        }
    }

    /**
     * Generate a unique username with configurable pattern
     *
     * @param int|null $index
     * @return string
     */
    public function generateUsername(?int $index = null): string
    {
        $prefix = config('provisioning.username_prefix', 'HS');
        $timestamp = now()->format('YmdHis');
        
        if ($index !== null) {
            $suffix = str_pad((string) $index, 3, '0', STR_PAD_LEFT);
        } else {
            $suffix = strtoupper(Str::random(3));
        }

        return $prefix . $timestamp . $suffix;
    }

    /**
     * Generate a random password
     *
     * @return string
     */
    public function generatePassword(): string
    {
        $length = config('provisioning.password_length', 10);
        
        // Generate password with mixed alphanumeric characters
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }
}