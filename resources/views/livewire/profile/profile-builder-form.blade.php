<?php

use App\Models\Profile;
use App\Models\SocialLink;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Component;

new class extends Component
{
    // Profile fields
    public string $slug = '';
    public ?string $title = null;
    public ?string $company = null;
    public ?string $bio = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $website = null;
    public ?string $address = null;
    public bool $is_public = true;
    
    // Image uploads
    public ?TemporaryUploadedFile $profile_image = null;
    public ?TemporaryUploadedFile $cover_image = null;
    
    // Social links
    public array $socialLinks = [];
    
    // Display fields
    public ?string $existing_profile_image = null;
    public ?string $existing_cover_image = null;
    public bool $showPreview = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $profile = $user->profile;

        if ($profile) {
            $this->slug = $profile->slug;
            $this->title = $profile->title;
            $this->company = $profile->company;
            $this->bio = $profile->bio;
            $this->phone = $profile->phone;
            $this->email = $profile->email;
            $this->website = $profile->website;
            $this->address = $profile->address;
            $this->is_public = $profile->is_public;
            $this->existing_profile_image = $profile->profile_image;
            $this->existing_cover_image = $profile->cover_image;
            
            // Load social links
            $this->socialLinks = $profile->socialLinks->map(function ($link) {
                return [
                    'id' => $link->id,
                    'platform' => $link->platform,
                    'label' => $link->label,
                    'url' => $link->url,
                    'icon' => $link->icon,
                    'order' => $link->order,
                ];
            })->toArray();
        } else {
            // Generate initial slug from user name
            $this->slug = Profile::generateUniqueSlug($user->name);
        }
    }

    /**
     * Add a new social link.
     */
    public function addSocialLink(): void
    {
        $this->socialLinks[] = [
            'id' => null,
            'platform' => 'custom',
            'label' => '',
            'url' => '',
            'icon' => '',
            'order' => count($this->socialLinks),
        ];
    }

    /**
     * Remove a social link.
     */
    public function removeSocialLink(int $index): void
    {
        unset($this->socialLinks[$index]);
        $this->socialLinks = array_values($this->socialLinks); // Reindex array
    }

    /**
     * Update the profile.
     */
    public function save(): void
    {
        $user = Auth::user();
        
        // Validate profile data
        $validated = $this->validate([
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', 'unique:profiles,slug,' . ($user->profile?->id ?? 'NULL') . ',id'],
            'title' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:500'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['boolean'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'socialLinks' => ['array'],
            'socialLinks.*.platform' => ['required_with:socialLinks.*.url', 'string', 'max:255'],
            'socialLinks.*.label' => ['required_with:socialLinks.*.url', 'string', 'max:255'],
            'socialLinks.*.url' => ['required_with:socialLinks.*.label', 'url', 'max:500'],
            'socialLinks.*.icon' => ['nullable', 'string', 'max:255'],
        ]);

        // Get or create profile
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        
        // Handle profile image upload
        if ($this->profile_image) {
            if ($profile->profile_image) {
                Storage::disk('public')->delete($profile->profile_image);
            }
            $validated['profile_image'] = $this->profile_image->store('profiles', 'public');
        }
        
        // Handle cover image upload
        if ($this->cover_image) {
            if ($profile->cover_image) {
                Storage::disk('public')->delete($profile->cover_image);
            }
            $validated['cover_image'] = $this->cover_image->store('profiles', 'public');
        }
        
        // Remove image from validated if not uploaded
        unset($validated['profile_image'], $validated['cover_image'], $validated['socialLinks']);
        
        // Update or create profile
        $profile->fill($validated);
        $profile->save();
        
        // Handle social links
        $existingLinkIds = collect($this->socialLinks)->pluck('id')->filter()->toArray();
        
        // Delete removed links
        $profile->socialLinks()->whereNotIn('id', $existingLinkIds)->delete();
        
        // Update or create social links
        foreach ($this->socialLinks as $index => $linkData) {
            if (empty($linkData['url']) || empty($linkData['label'])) {
                continue; // Skip empty links
            }
            
            $linkData['order'] = $index;
            $linkData['profile_id'] = $profile->id;
            
            if ($linkData['id']) {
                $profile->socialLinks()->where('id', $linkData['id'])->update(
                    collect($linkData)->except('id')->toArray()
                );
            } else {
                unset($linkData['id']);
                $profile->socialLinks()->create($linkData);
            }
        }
        
        // Refresh existing images
        $profile->refresh();
        $this->existing_profile_image = $profile->profile_image;
        $this->existing_cover_image = $profile->cover_image;
        
        // Clear temporary uploads
        $this->profile_image = null;
        $this->cover_image = null;
        
        session()->flash('status', 'Profile saved successfully!');
    }

    /**
     * Toggle preview mode.
     */
    public function togglePreview(): void
    {
        $this->showPreview = !$this->showPreview;
    }
}; ?>

<section>
    <header>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ __('Digital Profile Builder') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Create and manage your digital identity profile. This profile will be accessible via your NFC card or QR code.') }}
                </p>
            </div>
            <button 
                type="button" 
                wire:click="togglePreview"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
            >
                {{ $showPreview ? 'Edit' : 'Preview' }}
            </button>
        </div>
    </header>

    @if (session('status'))
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('status') }}</p>
        </div>
    @endif

    @if (!$showPreview)
        <form wire:submit="save" class="mt-6 space-y-6">
            <!-- Profile Slug -->
            <div>
                <x-input-label for="slug" :value="__('Profile URL Slug')" />
                <div class="mt-1 flex rounded-md shadow-sm">
                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-sm">
                        {{ config('app.url') }}/p/
                    </span>
                    <x-text-input 
                        wire:model="slug" 
                        id="slug" 
                        name="slug" 
                        type="text" 
                        class="block w-full rounded-none rounded-r-md border-gray-300 dark:border-gray-600" 
                        required 
                    />
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Only lowercase letters, numbers, and hyphens allowed</p>
            </div>

            <!-- Images Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Profile Image -->
                <div>
                    <x-input-label :value="__('Profile Image')" />
                    <div class="mt-2">
                        @if ($existing_profile_image || $profile_image)
                            <img 
                                src="{{ $profile_image ? $profile_image->temporaryUrl() : asset('storage/' . $existing_profile_image) }}" 
                                alt="Profile image preview" 
                                class="h-32 w-32 object-cover rounded-full border-2 border-gray-300 dark:border-gray-600"
                            />
                        @endif
                        <input 
                            wire:model="profile_image" 
                            type="file" 
                            accept="image/*" 
                            class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-200 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800"
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max 2MB. Recommended: Square image, 400x400px</p>
                    </div>
                </div>

                <!-- Cover Image -->
                <div>
                    <x-input-label :value="__('Cover Image (Optional)')" />
                    <div class="mt-2">
                        @if ($existing_cover_image || $cover_image)
                            <img 
                                src="{{ $cover_image ? $cover_image->temporaryUrl() : asset('storage/' . $existing_cover_image) }}" 
                                alt="Cover image preview" 
                                class="h-32 w-full object-cover rounded-lg border-2 border-gray-300 dark:border-gray-600"
                            />
                        @endif
                        <input 
                            wire:model="cover_image" 
                            type="file" 
                            accept="image/*" 
                            class="mt-2 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-200 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800"
                        />
                        <x-input-error class="mt-2" :messages="$errors->get('cover_image')" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max 5MB. Recommended: 1200x400px</p>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="title" :value="__('Job Title')" />
                    <x-text-input wire:model="title" id="title" name="title" type="text" class="mt-1 block w-full" />
                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                </div>

                <div>
                    <x-input-label for="company" :value="__('Company')" />
                    <x-text-input wire:model="company" id="company" name="company" type="text" class="mt-1 block w-full" />
                    <x-input-error class="mt-2" :messages="$errors->get('company')" />
                </div>
            </div>

            <!-- Bio -->
            <div>
                <x-input-label for="bio" :value="__('Bio / About')" />
                <textarea 
                    wire:model="bio" 
                    id="bio" 
                    name="bio" 
                    rows="4" 
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                ></textarea>
                <x-input-error class="mt-2" :messages="$errors->get('bio')" />
            </div>

            <!-- Contact Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="phone" :value="__('Phone')" />
                    <x-text-input wire:model="phone" id="phone" name="phone" type="tel" class="mt-1 block w-full" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="website" :value="__('Website')" />
                    <x-text-input wire:model="website" id="website" name="website" type="url" class="mt-1 block w-full" placeholder="https://example.com" />
                    <x-input-error class="mt-2" :messages="$errors->get('website')" />
                </div>

                <div>
                    <x-input-label for="address" :value="__('Address')" />
                    <x-text-input wire:model="address" id="address" name="address" type="text" class="mt-1 block w-full" />
                    <x-input-error class="mt-2" :messages="$errors->get('address')" />
                </div>
            </div>

            <!-- Social Links -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <x-input-label :value="__('Social Links')" />
                    <button 
                        type="button" 
                        wire:click="addSocialLink"
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                    >
                        + Add Link
                    </button>
                </div>

                <div class="space-y-4">
                    @foreach ($socialLinks as $index => $link)
                        <div class="flex gap-4 items-start p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label :value="__('Platform')" />
                                    <select 
                                        wire:model="socialLinks.{{ $index }}.platform"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="custom">Custom</option>
                                        <option value="linkedin">LinkedIn</option>
                                        <option value="twitter">Twitter</option>
                                        <option value="instagram">Instagram</option>
                                        <option value="facebook">Facebook</option>
                                        <option value="github">GitHub</option>
                                        <option value="youtube">YouTube</option>
                                        <option value="tiktok">TikTok</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :value="__('Label')" />
                                    <x-text-input wire:model="socialLinks.{{ $index }}.label" type="text" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label :value="__('URL')" />
                                    <x-text-input wire:model="socialLinks.{{ $index }}.url" type="url" class="mt-1 block w-full" placeholder="https://" />
                                </div>
                            </div>
                            <button 
                                type="button" 
                                wire:click="removeSocialLink({{ $index }})"
                                class="mt-6 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        @if ($errors->has("socialLinks.{$index}.*"))
                            <x-input-error :messages="$errors->get("socialLinks.{$index}.*")" />
                        @endif
                    @endforeach

                    @if (empty($socialLinks))
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No social links added yet. Click "Add Link" to add one.</p>
                    @endif
                </div>
            </div>

            <!-- Public Toggle -->
            <div class="flex items-center">
                <input 
                    wire:model="is_public" 
                    id="is_public" 
                    type="checkbox" 
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded"
                >
                <x-input-label for="is_public" :value="__('Make profile publicly accessible')" class="ml-2" />
            </div>

            <!-- Submit Button -->
            <div class="flex items-center gap-4">
                <x-primary-button wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Save Profile') }}</span>
                    <span wire:loading>Saving...</span>
                </x-primary-button>

                <x-action-message class="me-3" on="profile-saved">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <!-- QR Code Section (shown after profile is saved) -->
        @if (Auth::user()->profile && Auth::user()->profile->id)
            <div class="mt-8 p-6 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Your QR Code</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Share this QR code to let people quickly access your profile. Scan it with any QR code reader.
                </p>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                        <img src="{{ Auth::user()->profile->qr_code_url }}" 
                             alt="QR Code for {{ Auth::user()->profile->public_url }}" 
                             class="w-48 h-48">
                    </div>
                    <div class="flex-1">
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profile URL:</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 break-all font-mono bg-white dark:bg-gray-800 p-2 rounded border border-gray-200 dark:border-gray-600">
                                {{ Auth::user()->profile->public_url }}
                            </p>
                        </div>
                        <a href="{{ Auth::user()->profile->qr_code_url }}&download=1" 
                           download="linkadi-profile-qr-{{ Auth::user()->profile->slug }}.png"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download QR Code
                        </a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            High-resolution PNG format (500x500px)
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- Preview Mode -->
        <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            @if ($existing_cover_image)
                <div class="h-48 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $existing_cover_image) }}')"></div>
            @endif
            
            <div class="p-6">
                <div class="flex items-center gap-6">
                    @if ($existing_profile_image)
                        <img src="{{ asset('storage/' . $existing_profile_image) }}" alt="Profile" class="h-24 w-24 rounded-full border-4 border-white dark:border-gray-800 shadow-lg">
                    @else
                        <div class="h-24 w-24 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                            <span class="text-2xl text-gray-500 dark:text-gray-400">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                    @endif
                    
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ Auth::user()->name }}</h3>
                        @if ($title || $company)
                            <p class="text-gray-600 dark:text-gray-400">
                                @if ($title){{ $title }}@endif
                                @if ($title && $company) at @endif
                                @if ($company){{ $company }}@endif
                            </p>
                        @endif
                    </div>
                </div>
                
                @if ($bio)
                    <div class="mt-6">
                        <p class="text-gray-700 dark:text-gray-300">{{ $bio }}</p>
                    </div>
                @endif
                
                @if (count(array_filter($socialLinks, fn($link) => !empty($link['url']) && !empty($link['label']))) > 0)
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Links</h4>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($socialLinks as $link)
                                @if (!empty($link['url']) && !empty($link['label']))
                                    <a href="{{ $link['url'] }}" target="_blank" class="px-4 py-2 bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-800">
                                        {{ $link['label'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</section>

