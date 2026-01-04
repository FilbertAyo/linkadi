<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfilePublishable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to perform this action.');
        }

        $profile = $user->profile;

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

        return $next($request);
    }
}
