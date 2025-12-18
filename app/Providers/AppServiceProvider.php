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
        // Auto-create profile when user is created
        User::created(function (User $user) {
            if (!$user->profile) {
                $profile = new Profile([
                    'user_id' => $user->id,
                    'slug' => Profile::generateUniqueSlug($user->name),
                    'is_public' => true,
                ]);
                $profile->save();
            }
        });
    }
}
