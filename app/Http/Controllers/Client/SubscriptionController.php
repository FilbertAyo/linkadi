<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected $subscriptionService;
    
    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }
    
    /**
     * Display subscription management page.
     */
    public function index()
    {
        $profiles = Auth::user()->profiles()
            ->with(['package', 'nfcCards' => function($query) {
                $query->whereIn('status', ['activated', 'delivered', 'shipped']);
            }])
            ->get();
        
        // Get statistics
        $stats = $this->subscriptionService->getUserSubscriptionStats(Auth::user());
        
        // Separate profiles by status
        $activeProfiles = $profiles->where('status', 'published');
        $expiringProfiles = $profiles->filter(fn($p) => $p->isExpiringSoon());
        $expiredProfiles = $profiles->where('status', 'expired');
        $draftProfiles = $profiles->where('status', 'draft');
        
        return view('client.subscriptions.index', compact(
            'profiles',
            'stats',
            'activeProfiles',
            'expiringProfiles',
            'expiredProfiles',
            'draftProfiles'
        ));
    }
    
    /**
     * Show subscription details for a profile.
     */
    public function show(Profile $profile)
    {
        // Ensure user owns the profile
        if ($profile->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this subscription.');
        }
        
        $profile->load(['package', 'order']);
        
        return view('client.subscriptions.show', compact('profile'));
    }
    
    /**
     * Renew a single profile subscription.
     */
    public function renew(Profile $profile, Request $request)
    {
        // Ensure user owns the profile
        if ($profile->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this profile.');
        }
        
        // Check if profile is in a renewable state
        if (!in_array($profile->status, ['published', 'expired'])) {
            return back()->with('error', 'This profile cannot be renewed at this time.');
        }
        
        // Validate subscription years
        $validated = $request->validate([
            'years' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);
        
        $years = $validated['years'] ?? 1;
        
        try {
            $order = $this->subscriptionService->renewSubscription($profile, $years);
            
            $message = $years > 1 ? "Renewal order created for {$years} years. Please complete payment." : 'Renewal order created. Please complete payment.';
            
            return redirect()->route('dashboard.orders.payment', $order)
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to create renewal order', [
                'profile_id' => $profile->id,
                'user_id' => Auth::id(),
                'years' => $years,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Failed to create renewal order: ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk renew multiple profiles.
     */
    public function bulkRenew(Request $request)
    {
        $validated = $request->validate([
            'profile_ids' => 'required|array|min:1',
            'profile_ids.*' => 'required|exists:profiles,id',
            'years' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);
        
        // Validate all profiles belong to user
        $userProfileCount = Auth::user()->profiles()
            ->whereIn('id', $validated['profile_ids'])
            ->count();
            
        if ($userProfileCount !== count($validated['profile_ids'])) {
            return back()->withErrors(['profile_ids' => 'One or more profiles do not belong to you.']);
        }
        
        $years = $validated['years'] ?? 1;
        
        try {
            $order = $this->subscriptionService->bulkRenewSubscriptions(
                $validated['profile_ids'],
                Auth::user(),
                $years
            );
            
            $profileCount = count($validated['profile_ids']);
            $discountMsg = $profileCount >= 3 || $years >= 2 ? 'Discount applied!' : '';
            
            return redirect()->route('dashboard.orders.payment', $order)
                ->with('success', "Bulk renewal order created for {$profileCount} " . \Illuminate\Support\Str::plural('profile', $profileCount) . " ({$years} " . \Illuminate\Support\Str::plural('year', $years) . "). {$discountMsg} Please complete payment.");
        } catch (\Exception $e) {
            Log::error('Failed to create bulk renewal order', [
                'profile_ids' => $validated['profile_ids'],
                'user_id' => Auth::id(),
                'years' => $years,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Failed to create bulk renewal order: ' . $e->getMessage());
        }
    }
}
