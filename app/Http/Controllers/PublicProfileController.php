<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\VCardGeneratorService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class PublicProfileController extends Controller
{
    protected VCardGeneratorService $vCardGenerator;

    public function __construct(VCardGeneratorService $vCardGenerator)
    {
        $this->vCardGenerator = $vCardGenerator;
    }
    /**
     * Display the public profile page.
     */
    public function show(string $slug): View|Response
    {
        $profile = Profile::where('slug', $slug)
            ->with(['user', 'socialLinks', 'order'])
            ->firstOrFail();

        // Check if profile is publicly accessible
        $isOwner = auth()->check() && auth()->id() === $profile->user_id;
        
        // If not the owner, check if profile is publicly accessible
        if (!$isOwner && !$profile->isPubliclyAccessible()) {
            abort(404);
        }

        return view('public-profile', compact('profile'));
    }

    /**
     * Download vCard for the profile.
     */
    public function downloadVCard(string $slug): Response
    {
        $profile = Profile::where('slug', $slug)
            ->with(['user', 'contacts', 'order'])
            ->firstOrFail();

        // Check if profile is publicly accessible
        $isOwner = auth()->check() && auth()->id() === $profile->user_id;
        
        if (!$isOwner && !$profile->isPubliclyAccessible()) {
            abort(404);
        }

        // Generate vCard
        $vCardContent = $this->vCardGenerator->generate($profile);
        $filename = $this->vCardGenerator->getFilename($profile);

        return response($vCardContent, 200)
            ->header('Content-Type', 'text/vcard; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($vCardContent));
    }
}
