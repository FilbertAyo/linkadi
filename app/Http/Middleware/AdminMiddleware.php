<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            abort(403, 'Unauthorized access.');
        }

        // Check if user has admin role
        if (!auth()->user()->hasRole('admin')) {
            // Log unauthorized access attempt
            \Log::warning('Unauthorized admin access attempt', [
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            abort(403, 'You do not have permission to access this area.');
        }

        // Rate limiting for admin routes (more strict)
        $key = 'admin:' . $request->ip() . ':' . auth()->id();
        
        if (RateLimiter::tooManyAttempts($key, 60)) { // 60 requests per minute
            $seconds = RateLimiter::availableIn($key);
            
            \Log::warning('Admin rate limit exceeded', [
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            abort(429, 'Too many requests. Please try again in ' . ceil($seconds / 60) . ' minute(s).');
        }

        RateLimiter::hit($key, 60); // 60 seconds decay

        return $next($request);
    }
}
