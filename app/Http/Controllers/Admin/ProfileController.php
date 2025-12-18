<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Profile::class);

        $query = Profile::with(['user', 'socialLinks'])->latest();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by public/private
        if ($request->has('visibility') && $request->visibility !== '') {
            $query->where('is_public', $request->visibility === 'public');
        }

        $profiles = $query->paginate(15)->withQueryString();

        return view('admin.profiles.index', compact('profiles'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        $this->authorize('view', $profile);

        $profile->load(['user', 'socialLinks']);
        $auditLogs = AuditLog::where('model_type', Profile::class)
            ->where('model_id', $profile->id)
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.profiles.show', compact('profile', 'auditLogs'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Profile $profile)
    {
        $this->authorize('update', $profile);

        $profile->load(['user', 'socialLinks']);

        return view('admin.profiles.edit', compact('profile'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        $this->authorize('update', $profile);

        $oldValues = $profile->only([
            'title', 'company', 'bio', 'phone', 'email', 
            'website', 'address', 'is_public'
        ]);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_public' => ['boolean'],
        ]);

        $profile->update($validated);

        $newValues = $profile->fresh()->only([
            'title', 'company', 'bio', 'phone', 'email', 
            'website', 'address', 'is_public'
        ]);

        // Audit log
        AuditLog::log('profile.updated', $profile, $oldValues, $newValues, "Profile updated by admin");

        return redirect()->route('admin.profiles.index')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile)
    {
        $this->authorize('delete', $profile);

        $oldValues = $profile->only(['id', 'slug', 'title', 'user_id']);
        $profileSlug = $profile->slug;

        $profile->delete();

        // Audit log
        AuditLog::log('profile.deleted', null, $oldValues, [], "Profile {$profileSlug} deleted by admin");

        return redirect()->route('admin.profiles.index')
            ->with('success', 'Profile deleted successfully.');
    }
}
