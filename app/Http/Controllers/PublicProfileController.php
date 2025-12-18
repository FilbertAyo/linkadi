<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class PublicProfileController extends Controller
{
    /**
     * Display the public profile page.
     */
    public function show(string $slug): View|Response
    {
        $profile = Profile::where('slug', $slug)
            ->with(['user', 'socialLinks'])
            ->firstOrFail();

        // Check if profile is public or if user owns it
        if (!$profile->is_public && (!auth()->check() || auth()->id() !== $profile->user_id)) {
            abort(404);
        }

        return view('public-profile', compact('profile'));
    }
}
