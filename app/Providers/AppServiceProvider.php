<?php

namespace App\Providers;

use App\Listeners\SendWelcomeEmail;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
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
        // Register event listeners
        Event::listen(
            Registered::class,
            SendWelcomeEmail::class,
        );

        // Auto-create primary profile when user is created
        User::created(function (User $user) {
            if ($user->profiles()->count() === 0) {
                $profile = new Profile([
                    'user_id' => $user->id,
                    'profile_name' => 'My Profile',
                    'slug' => Profile::generateUniqueSlug($user->name),
                    'is_public' => true,
                    'is_primary' => true,
                    'display_mode' => 'personal_only',
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
