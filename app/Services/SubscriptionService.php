<?php

namespace App\Services;

use App\Models\NfcCard;
use App\Models\Order;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\SubscriptionExpired;
use App\Notifications\SubscriptionExpiring;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    /**
     * Process subscription renewal for a profile.
     * 
     * @param Profile $profile The profile to renew
     * @param int $years Number of years to renew for (default: 1)
     * @return Order The renewal order created
     */
    public function renewSubscription(Profile $profile, int $years = 1): Order
    {
        $package = $profile->package;
        
        if (!$package) {
            throw new \Exception('Profile does not have an associated package.');
        }
        
        // Calculate renewal price with potential discount
        $renewalPricing = $package->calculateRenewalPrice($years);
        $renewalPrice = $renewalPricing['price'];
        $discount = $renewalPricing['savings'] ?? 0;
        
        $order = Order::create([
            'user_id' => $profile->user_id,
            'package_id' => $package->id,
            'profile_id' => $profile->id,
            'quantity' => 1,
            'unit_price' => $renewalPrice,
            'base_price' => $renewalPrice,
            'subscription_price' => $renewalPrice,
            'subscription_years' => $years,
            'subscription_discount' => $discount,
            'printing_fee' => 0,
            'design_fee' => 0,
            'total_price' => $renewalPrice,
            'status' => 'pending',
            'payment_status' => 'pending',
            'notes' => "Subscription renewal for {$years} " . \Illuminate\Support\Str::plural('year', $years) . " - Profile: {$profile->slug}",
        ]);
        
        // Update profile status
        $profile->status = 'pending_payment';
        $profile->order_id = $order->id;
        $profile->save();
        
        Log::info('Subscription renewal order created', [
            'profile_id' => $profile->id,
            'order_id' => $order->id,
            'years' => $years,
            'amount' => $renewalPrice,
            'discount' => $discount,
        ]);
        
        return $order;
    }
    
    /**
     * Renew multiple profiles at once (bulk renewal).
     * 
     * @param array $profileIds Array of profile IDs to renew
     * @param User $user The user performing the renewal
     * @param int $years Number of years to renew for (default: 1)
     * @return Order The bulk renewal order
     */
    public function bulkRenewSubscriptions(array $profileIds, User $user, int $years = 1): Order
    {
        $profiles = Profile::whereIn('id', $profileIds)
            ->where('user_id', $user->id)
            ->get();
            
        if ($profiles->isEmpty()) {
            throw new \Exception('No valid profiles found for renewal.');
        }
        
        // Get the first package (assuming all profiles have similar packages)
        $package = $profiles->first()->package;
        
        if (!$package) {
            throw new \Exception('Profiles do not have an associated package.');
        }
        
        // Calculate price per profile with multi-year discount
        $renewalPricing = $package->calculateRenewalPrice($years);
        $pricePerProfile = $renewalPricing['price'];
        $discountPerProfile = $renewalPricing['savings'] ?? 0;
        
        $quantity = $profiles->count();
        $totalPrice = $pricePerProfile * $quantity;
        $totalDiscount = $discountPerProfile * $quantity;
        
        // Additional bulk discount for multiple profiles (5% for 3+ profiles)
        if ($quantity >= 3) {
            $bulkDiscount = $totalPrice * 0.05;
            $totalPrice -= $bulkDiscount;
            $totalDiscount += $bulkDiscount;
        }
        
        $order = Order::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'quantity' => $quantity,
            'unit_price' => $totalPrice / $quantity,
            'base_price' => $totalPrice,
            'subscription_price' => $totalPrice,
            'subscription_years' => $years,
            'subscription_discount' => $totalDiscount,
            'printing_fee' => 0,
            'design_fee' => 0,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'payment_status' => 'pending',
            'notes' => "Bulk renewal for {$quantity} " . \Illuminate\Support\Str::plural('profile', $quantity) . " - {$years} " . \Illuminate\Support\Str::plural('year', $years),
        ]);
        
        // Link all profiles to this order
        foreach ($profiles as $profile) {
            $profile->status = 'pending_payment';
            $profile->order_id = $order->id;
            $profile->save();
        }
        
        Log::info('Bulk subscription renewal order created', [
            'user_id' => $user->id,
            'order_id' => $order->id,
            'profiles_count' => $quantity,
            'years' => $years,
            'total_amount' => $totalPrice,
            'total_discount' => $totalDiscount,
        ]);
        
        return $order;
    }
    
    /**
     * Check and expire subscriptions (run daily via scheduler).
     * 
     * @return int Number of profiles expired
     */
    public function checkExpiredSubscriptions(): int
    {
        $expiredCount = 0;
        
        // Expire profiles
        Profile::where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->chunk(100, function ($profiles) use (&$expiredCount) {
                foreach ($profiles as $profile) {
                    $profile->update(['status' => 'expired']);
                    $expiredCount++;
                    
                    // Notify user about expiration
                    try {
                        $profile->user->notify(new SubscriptionExpired($profile));
                    } catch (\Exception $e) {
                        Log::error('Failed to send subscription expired notification', [
                            'profile_id' => $profile->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
            
        // Expire NFC cards
        NfcCard::where('status', 'activated')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);
        
        Log::info('Subscription expiration check completed', [
            'profiles_expired' => $expiredCount,
        ]);
        
        return $expiredCount;
    }
    
    /**
     * Notify users about expiring subscriptions (30 days before).
     * 
     * @return int Number of notifications sent
     */
    public function notifyExpiringSubscriptions(): int
    {
        $notificationCount = 0;
        
        Profile::where('status', 'published')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now()->addDays(29), now()->addDays(31)])
            ->chunk(100, function ($profiles) use (&$notificationCount) {
                foreach ($profiles as $profile) {
                    try {
                        $profile->user->notify(new SubscriptionExpiring($profile));
                        $notificationCount++;
                    } catch (\Exception $e) {
                        Log::error('Failed to send subscription expiring notification', [
                            'profile_id' => $profile->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
        
        Log::info('Subscription expiring notifications sent', [
            'notifications_sent' => $notificationCount,
        ]);
        
        return $notificationCount;
    }
    
    
    /**
     * Get profiles expiring within specified days.
     * 
     * @param int $days Number of days to check (default: 30)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExpiringProfiles(int $days = 30)
    {
        return         Profile::where('status', 'published')
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)])
            ->with('user', 'package')
            ->get();
    }
    
    /**
     * Get subscription statistics for a user.
     * 
     * @param User $user
     * @return array
     */
    public function getUserSubscriptionStats(User $user): array
    {
        $profiles = $user->profiles;
        
        return [
            'total_profiles' => $profiles->count(),
            'active_profiles' => $profiles->where('status', 'published')->count(),
            'draft_profiles' => $profiles->where('status', 'draft')->count(),
            'expired_profiles' => $profiles->where('status', 'expired')->count(),
            'expiring_soon' => $profiles->filter(fn($p) => $p->isExpiringSoon())->count(),
            'pending_payment' => $profiles->where('status', 'pending_payment')->count(),
        ];
    }
}

