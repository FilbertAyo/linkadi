<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfilePublishingService
{
    /**
     * Publish a profile.
     *
     * @param Profile $profile
     * @return bool
     * @throws \Exception
     */
    public function publish(Profile $profile): bool
    {
        // Check if profile can be published
        if (!$profile->canPublish()) {
            throw new \Exception('Profile cannot be published. Status: ' . $profile->status);
        }

        try {
            DB::beginTransaction();

            // Update profile status
            $profile->status = 'published';
            $profile->published_at = now();
            $profile->is_public = true;
            $profile->save();

            // Log the publishing event
            Log::info('Profile published', [
                'profile_id' => $profile->id,
                'user_id' => $profile->user_id,
                'published_at' => $profile->published_at,
            ]);

            // Dispatch event (for notifications, analytics, etc.)
            // event(new ProfilePublished($profile));

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to publish profile', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Unpublish a profile.
     *
     * @param Profile $profile
     * @return bool
     */
    public function unpublish(Profile $profile): bool
    {
        if (!$profile->isPublished()) {
            throw new \Exception('Profile is not published.');
        }

        try {
            DB::beginTransaction();

            // Revert to paid status
            $profile->status = 'paid';
            $profile->is_public = false;
            $profile->save();

            // Log the unpublishing event
            Log::info('Profile unpublished', [
                'profile_id' => $profile->id,
                'user_id' => $profile->user_id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to unpublish profile', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark profile as expired.
     *
     * @param Profile $profile
     * @return bool
     */
    public function expire(Profile $profile): bool
    {
        try {
            DB::beginTransaction();

            $profile->status = 'expired';
            $profile->is_public = false;
            $profile->save();

            // Log the expiry event
            Log::info('Profile expired', [
                'profile_id' => $profile->id,
                'user_id' => $profile->user_id,
                'expired_at' => now(),
            ]);

            // Send notification to user
            // $profile->user->notify(new ProfileExpiredNotification($profile));

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to expire profile', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Suspend a profile (admin action).
     *
     * @param Profile $profile
     * @param string|null $reason
     * @return bool
     */
    public function suspend(Profile $profile, ?string $reason = null): bool
    {
        try {
            DB::beginTransaction();

            $profile->status = 'suspended';
            $profile->is_public = false;
            $profile->save();

            // Log the suspension
            Log::warning('Profile suspended', [
                'profile_id' => $profile->id,
                'user_id' => $profile->user_id,
                'reason' => $reason,
            ]);

            // Send notification to user
            // $profile->user->notify(new ProfileSuspendedNotification($profile, $reason));

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to suspend profile', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Resume a suspended profile (admin action).
     *
     * @param Profile $profile
     * @return bool
     */
    public function resume(Profile $profile): bool
    {
        if (!$profile->isSuspended()) {
            throw new \Exception('Profile is not suspended.');
        }

        try {
            DB::beginTransaction();

            // Restore to previous status (either published or paid)
            $status = $profile->published_at ? 'published' : 'paid';
            $profile->status = $status;
            
            if ($status === 'published') {
                $profile->is_public = true;
            }
            
            $profile->save();

            // Log the resumption
            Log::info('Profile resumed', [
                'profile_id' => $profile->id,
                'user_id' => $profile->user_id,
                'new_status' => $status,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to resume profile', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark profile as ready (completed profile, ready for payment).
     *
     * @param Profile $profile
     * @return bool
     */
    public function markAsReady(Profile $profile): bool
    {
        if ($profile->isDraft()) {
            $profile->status = 'ready';
            $profile->save();
            return true;
        }
        
        return false;
    }
}

