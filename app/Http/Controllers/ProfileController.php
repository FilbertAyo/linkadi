<?php

namespace App\Http\Controllers;

use App\Services\ProfilePublishingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected ProfilePublishingService $publishingService;

    public function __construct(ProfilePublishingService $publishingService)
    {
        $this->publishingService = $publishingService;
    }

    /**
     * Publish the authenticated user's profile.
     */
    public function publish(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Get profile_id from request or use primary profile
        $profileId = $request->input('profile_id');
        
        if ($profileId) {
            $profile = $user->profiles()->findOrFail($profileId);
        } else {
            $profile = $user->primaryProfile ?? $user->profiles()->first();
        }

        if (!$profile) {
            return redirect()->route('profile.builder')
                ->with('error', 'Please create your profile first.');
        }

        if (!$profile->canPublish()) {
            $message = match($profile->status) {
                'draft' => 'Please complete your profile before publishing.',
                'ready' => 'Please purchase a package before publishing your profile.',
                'pending_payment' => 'Please complete your payment to publish your profile.',
                'published' => 'Your profile is already published.',
                'expired' => 'Your package has expired. Please renew to publish your profile.',
                'suspended' => 'Your profile has been suspended. Please contact support.',
                default => 'Your profile cannot be published at this time.',
            };

            return redirect()->route('dashboard')
                ->with('error', $message);
        }

        try {
            $this->publishingService->publish($profile);

            return redirect()->route('dashboard')
                ->with('success', '🎉 Your profile "' . ($profile->profile_name ?? 'Profile') . '" is now live! Share it with the world: ' . $profile->public_url);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Failed to publish profile: ' . $e->getMessage());
        }
    }

    /**
     * Unpublish the authenticated user's profile.
     */
    public function unpublish(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Get profile_id from request or use primary profile
        $profileId = $request->input('profile_id');
        
        if ($profileId) {
            $profile = $user->profiles()->findOrFail($profileId);
        } else {
            $profile = $user->primaryProfile ?? $user->profiles()->first();
        }

        if (!$profile) {
            return redirect()->route('dashboard')
                ->with('error', 'Profile not found.');
        }

        if (!$profile->isPublished()) {
            return redirect()->route('dashboard')
                ->with('error', 'Your profile is not published.');
        }

        try {
            $this->publishingService->unpublish($profile);

            return redirect()->route('dashboard')
                ->with('success', 'Your profile "' . ($profile->profile_name ?? 'Profile') . '" has been unpublished and is no longer publicly accessible.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Failed to unpublish profile: ' . $e->getMessage());
        }
    }
}
