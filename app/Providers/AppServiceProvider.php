<?php

namespace App\Providers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-create primary profile when user is created
        User::created(function (User $user) {
            if ($user->profiles()->count() === 0) {
                $profile = new Profile([
                    'user_id' => $user->id,
                    'profile_name' => 'My Profile',
                    'slug' => Profile::generateUniqueSlug($user->name),
                    'is_public' => true,
                    'is_primary' => true,
                    'display_mode' => 'combined',
                ]);
                $profile->save();
            }
        });

        // Make asset URLs work with network access (0.0.0.0)
        // This ensures assets work when accessing via network IP on mobile devices
        if (app()->runningInConsole() === false && request()->server('HTTP_HOST')) {
            $scheme = request()->getScheme();
            $host = request()->getHttpHost();
            $baseUrl = "{$scheme}://{$host}";
            config(['app.url' => $baseUrl]);
            // Also update storage URL for dynamic asset generation
            config(['filesystems.disks.public.url' => "{$baseUrl}/storage"]);
        }
    }
}
