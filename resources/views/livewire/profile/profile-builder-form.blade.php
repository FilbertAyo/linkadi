<?php

use App\Models\Profile;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;
    
    public bool $showPreview = false;
    public ?int $profileId = null;
    public bool $isCreatingNew = true;
    public ?int $current_profile_id = null;
    
    // Form fields - Basic
    public string $profile_name = '';
    public string $slug = '';
    public string $display_mode = 'combined';
    public bool $is_public = true;
    
    // Personal fields
    public ?string $title = null;
    public ?string $company = null;
    public ?string $personal_bio = null;
    public ?string $website = null;
    public ?string $address = null;
    
    // Company fields
    public ?string $business_name = null;
    public ?string $tax_id = null;
    public ?string $industry = null;
    public ?string $company_size = null;
    public ?string $company_bio = null;
    public ?string $company_email = null;
    public ?string $company_phone = null;
    public ?string $company_website = null;
    public ?string $company_address = null;
    public ?string $services_offered = null;
    
    // Social media URLs
    public ?string $linkedin_url = null;
    public ?string $facebook_url = null;
    public ?string $twitter_url = null;
    public ?string $instagram_url = null;
    
    // Images
    public $profile_image;
    public $company_logo;
    public $cover_image;
    public ?string $existing_profile_image = null;
    public ?string $existing_company_logo = null;
    public ?string $existing_cover_image = null;
    
    // Social links
    public array $socialLinks = [];
    
    public function mount(?int $profileId = null): void
    {
        $this->profileId = $profileId;
        $this->current_profile_id = $profileId;
        $this->isCreatingNew = $profileId === null;
        
        if ($this->profileId) {
            $profile = Auth::user()->profiles()->find($this->profileId);
            if ($profile) {
                $this->profile_name = $profile->profile_name ?? '';
                $this->slug = $profile->slug ?? ''; // Keep existing slug for display only
                $this->display_mode = $profile->display_mode ?? 'combined';
                
                // Personal fields
                $this->title = $profile->title ?? null;
                $this->company = $profile->company ?? null;
                $this->personal_bio = $profile->personal_bio ?? $profile->bio ?? null;
                $this->website = $profile->website ?? null;
                $this->address = $profile->address ?? null;
                
                // Company fields
                $this->business_name = $profile->business_name ?? null;
                $this->tax_id = $profile->tax_id ?? null;
                $this->industry = $profile->industry ?? null;
                $this->company_size = $profile->company_size ?? null;
                $this->company_bio = $profile->company_bio ?? null;
                $this->company_email = $profile->company_email ?? null;
                $this->company_phone = $profile->company_phone ?? null;
                $this->company_website = $profile->company_website ?? null;
                $this->company_address = $profile->company_address ?? null;
                $this->services_offered = $profile->services_offered ?? null;
                
                // Social media
                $this->linkedin_url = $profile->linkedin_url ?? null;
                $this->facebook_url = $profile->facebook_url ?? null;
                $this->twitter_url = $profile->twitter_url ?? null;
                $this->instagram_url = $profile->instagram_url ?? null;
                
                $this->is_public = $profile->is_public ?? true;
                $this->existing_profile_image = $profile->profile_image;
                $this->existing_company_logo = $profile->company_logo;
                $this->existing_cover_image = $profile->cover_image;
                
                // Load social links
                $this->socialLinks = $profile->socialLinks->map(function ($link) {
                    return [
                        'platform' => $link->platform ?? 'custom',
                        'label' => $link->label ?? '',
                        'url' => $link->url ?? '',
                    ];
                })->toArray();
            }
        }
        
        if (empty($this->socialLinks)) {
            $this->socialLinks = [];
        }
    }
    
    public function togglePreview(): void
    {
        $this->showPreview = !$this->showPreview;
    }
    
    /**
     * Auto-generate slug from profile_name when creating a new profile.
     * For existing profiles, slug remains unchanged.
     */
    public function updatedProfileName(): void
    {
        // Only auto-generate slug for new profiles
        if ($this->isCreatingNew && !empty($this->profile_name)) {
            $this->slug = Profile::generateUniqueSlug($this->profile_name);
        }
    }
    
    public function addSocialLink(): void
    {
        $this->socialLinks[] = [
            'platform' => 'custom',
            'label' => '',
            'url' => '',
        ];
    }
    
    public function removeSocialLink(int $index): void
    {
        unset($this->socialLinks[$index]);
        $this->socialLinks = array_values($this->socialLinks);
    }
    
    public function save(): void
    {
        $rules = [
            'profile_name' => ['required', 'string', 'max:255'],
            'display_mode' => ['required', 'in:personal_only,company_only,combined'],
            'is_public' => ['boolean'],
        ];
        
        // Conditional validation based on display_mode
        if ($this->display_mode === 'personal_only' || $this->display_mode === 'combined') {
            $rules['title'] = ['nullable', 'string', 'max:255'];
            $rules['company'] = ['nullable', 'string', 'max:255'];
            $rules['personal_bio'] = ['nullable', 'string', 'max:2000'];
            $rules['website'] = ['nullable', 'url', 'max:500'];
            $rules['address'] = ['nullable', 'string', 'max:500'];
        }
        
        if ($this->display_mode === 'company_only' || $this->display_mode === 'combined') {
            $rules['business_name'] = ['required', 'string', 'max:255'];
            $rules['tax_id'] = ['nullable', 'string', 'max:100'];
            $rules['industry'] = ['nullable', 'string', 'max:255'];
            $rules['company_size'] = ['nullable', 'string', 'max:100'];
            $rules['company_bio'] = ['nullable', 'string', 'max:2000'];
            $rules['company_email'] = ['nullable', 'email', 'max:255'];
            $rules['company_phone'] = ['nullable', 'string', 'max:50'];
            $rules['company_website'] = ['nullable', 'url', 'max:500'];
            $rules['company_address'] = ['nullable', 'string', 'max:500'];
            $rules['services_offered'] = ['nullable', 'string', 'max:1000'];
        }
        
        $rules['linkedin_url'] = ['nullable', 'url', 'max:500'];
        $rules['facebook_url'] = ['nullable', 'url', 'max:500'];
        $rules['twitter_url'] = ['nullable', 'url', 'max:500'];
        $rules['instagram_url'] = ['nullable', 'url', 'max:500'];
        $rules['profile_image'] = ['nullable', 'image', 'max:2048'];
        $rules['company_logo'] = ['nullable', 'image', 'max:2048'];
        $rules['cover_image'] = ['nullable', 'image', 'max:5120'];
        $rules['socialLinks.*.platform'] = ['nullable', 'string', 'max:255'];
        $rules['socialLinks.*.label'] = ['required_with:socialLinks.*.url', 'string', 'max:255'];
        $rules['socialLinks.*.url'] = ['required_with:socialLinks.*.label', 'url', 'max:500'];
        
        $validated = $this->validate($rules);
        
        // Auto-generate slug from profile_name
        // For new profiles: generate unique slug
        // For existing profiles: keep the existing slug (slug is not editable)
        if ($this->isCreatingNew) {
            $slug = Profile::generateUniqueSlug($validated['profile_name']);
        } else {
            // For existing profiles, use the current slug (don't allow changes)
            $profile = Auth::user()->profiles()->findOrFail($this->profileId);
            $slug = $profile->slug;
        }
        
        $data = [
            'user_id' => Auth::id(),
            'profile_name' => $validated['profile_name'],
            'slug' => $slug,
            'display_mode' => $validated['display_mode'],
            'is_public' => $validated['is_public'] ?? true,
        ];
        
        // Only set status to 'draft' for new profiles
        // For existing profiles, preserve the current status (e.g., paid, published)
        if ($this->isCreatingNew) {
            $data['status'] = 'draft';
        }
        
        // Add fields based on display mode
        if ($this->display_mode === 'personal_only' || $this->display_mode === 'combined') {
            $data['title'] = $this->title;
            $data['company'] = $this->company;
            $data['personal_bio'] = $this->personal_bio;
            $data['bio'] = $this->personal_bio; // Keep for backward compatibility
            $data['website'] = $this->website;
            $data['address'] = $this->address;
        }
        
        if ($this->display_mode === 'company_only' || $this->display_mode === 'combined') {
            $data['business_name'] = $this->business_name;
            $data['tax_id'] = $this->tax_id;
            $data['industry'] = $this->industry;
            $data['company_size'] = $this->company_size;
            $data['company_bio'] = $this->company_bio;
            $data['company_email'] = $this->company_email;
            $data['company_phone'] = $this->company_phone;
            $data['company_website'] = $this->company_website;
            $data['company_address'] = $this->company_address;
            $data['services_offered'] = $this->services_offered;
        }
        
        $data['linkedin_url'] = $this->linkedin_url;
        $data['facebook_url'] = $this->facebook_url;
        $data['twitter_url'] = $this->twitter_url;
        $data['instagram_url'] = $this->instagram_url;
        
        // Handle image uploads
        if ($this->profile_image && $this->profile_image instanceof TemporaryUploadedFile) {
            $path = $this->profile_image->store('profile-images', 'public');
            $data['profile_image'] = $path;
            
            if ($this->existing_profile_image) {
                Storage::disk('public')->delete($this->existing_profile_image);
            }
        }
        
        if ($this->company_logo && $this->company_logo instanceof TemporaryUploadedFile) {
            $path = $this->company_logo->store('company-logos', 'public');
            $data['company_logo'] = $path;
            
            if ($this->existing_company_logo) {
                Storage::disk('public')->delete($this->existing_company_logo);
            }
        }
        
        if ($this->cover_image && $this->cover_image instanceof TemporaryUploadedFile) {
            $path = $this->cover_image->store('cover-images', 'public');
            $data['cover_image'] = $path;
            
            if ($this->existing_cover_image) {
                Storage::disk('public')->delete($this->existing_cover_image);
            }
        }
        
        // Remember if we're creating new before we change state
        $wasCreatingNew = $this->isCreatingNew;
        
        // Create or update profile
        if ($this->profileId) {
            $profile = Auth::user()->profiles()->findOrFail($this->profileId);
            $profile->update($data);
        } else {
            $profile = Auth::user()->profiles()->create($data);
            $this->profileId = $profile->id;
            $this->current_profile_id = $profile->id;
            $this->isCreatingNew = false;
        }
        
        // Handle social links
        if (!empty($this->socialLinks)) {
            $profile->socialLinks()->delete();
            
            foreach ($this->socialLinks as $index => $link) {
                if (!empty($link['url']) && !empty($link['label'])) {
                    $profile->socialLinks()->create([
                        'platform' => $link['platform'] ?? 'custom',
                        'label' => $link['label'],
                        'url' => $link['url'],
                        'order' => $index,
                    ]);
                }
            }
        }
        
        session()->flash('status', 'Profile saved successfully!');
        $this->dispatch('profile-saved');
        
        // After creating new profile, redirect to package selection to order card
        // Note: We use $wasCreatingNew to check the state BEFORE we changed it above
        if ($wasCreatingNew) {
            $this->redirect(route('profile.select-package', ['profile' => $profile->id]), navigate: true);
        }
    }
    
    public function deleteProfile(): void
    {
        if (!$this->current_profile_id) {
            return;
        }
        
        $profile = Auth::user()->profiles()->findOrFail($this->current_profile_id);
        
        // Delete images
        if ($profile->profile_image) {
            Storage::disk('public')->delete($profile->profile_image);
        }
        if ($profile->company_logo) {
            Storage::disk('public')->delete($profile->company_logo);
        }
        if ($profile->cover_image) {
            Storage::disk('public')->delete($profile->cover_image);
        }
        
        $profile->delete();
        
        session()->flash('status', 'Profile deleted successfully!');
        $this->redirect(route('profile.builder.index'), navigate: true);
    }
    
}; ?>

<div class="w-full">
    <section>
        <header class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ __('Digital Profile Builder') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Create and manage your digital identity profile. This profile will be accessible via your NFC card or QR code.') }}
                    </p>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-800">{{ session('status') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        <div class="mt-6">
            <!-- Back Button -->
            <a href="{{ route('profile.builder.index') }}" class="mb-4 inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Profiles
            </a>

            <!-- Form Header -->
            <div class="mb-6 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ $isCreatingNew ? 'Create New Profile' : 'Edit Profile: ' . ($profile_name ?? 'Unnamed Profile') }}
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $isCreatingNew ? 'Fill in the details below to create your new digital profile.' : 'Update your profile information below' }}
                </p>
            </div>

            <form wire:submit="save" class="space-y-6">
                <!-- Profile Name -->
                <div>
                    <x-input-label for="profile_name" :value="__('Profile Name')" />
                    <x-text-input wire:model="profile_name" id="profile_name" type="text" class="mt-1 block w-full" placeholder="e.g., Personal Card, Company Card" required />
                    <x-input-error class="mt-2" :messages="$errors->get('profile_name')" />
                    <p class="mt-1 text-xs text-gray-500">Give this profile a name to help you identify it</p>
                </div>

                <!-- Display Mode Selection -->
                <div>
                    <x-input-label for="display_mode" :value="__('What Information to Display')" />
                    <select wire:model.live="display_mode" id="display_mode" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="personal_only">Personal Only</option>
                        <option value="company_only">Company Only</option>
                        <option value="combined">Combined (Both Personal & Company)</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('display_mode')" />
                    <p class="mt-1 text-xs text-gray-500">
                        @if($display_mode === 'personal_only')
                            Show only your personal information and links
                        @elseif($display_mode === 'company_only')
                            Show only your company/business information
                        @else
                            Show both personal and company information
                        @endif
                    </p>
                </div>

                <!-- Profile Slug (Read-only) -->
                <div>
                    <x-input-label for="slug" :value="__('Profile URL Slug')" />
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                            {{ config('app.url') }}/p/
                        </span>
                        <x-text-input 
                            wire:model="slug" 
                            id="slug" 
                            type="text" 
                            class="block w-full rounded-none rounded-r-md border-gray-300 bg-gray-50 cursor-not-allowed" 
                            readonly 
                        />
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        @if($isCreatingNew)
                            The URL slug is automatically generated from your profile name and will be globally unique.
                        @else
                            The URL slug cannot be changed after creation.
                        @endif
                    </p>
                </div>

                <!-- Images Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($display_mode === 'personal_only' || $display_mode === 'combined')
                        <div>
                            <x-input-label :value="__('Personal Profile Image')" />
                            <div class="mt-2">
                                @if ($existing_profile_image || $profile_image)
                                    <img src="{{ $profile_image ? $profile_image->temporaryUrl() : ($existing_profile_image ? asset('storage/' . $existing_profile_image) : '') }}" alt="Profile image preview" class="h-32 w-32 object-cover rounded-full border-2 border-gray-300" />
                                @endif
                                <input wire:model="profile_image" type="file" accept="image/*" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />
                                <p class="mt-1 text-xs text-gray-500">Max 2MB. Recommended: 400x400px</p>
                            </div>
                        </div>
                    @endif

                    @if($display_mode === 'company_only' || $display_mode === 'combined')
                        <div>
                            <x-input-label :value="__('Company Logo')" />
                            <div class="mt-2">
                                @if ($existing_company_logo || $company_logo)
                                    <img src="{{ $company_logo ? $company_logo->temporaryUrl() : ($existing_company_logo ? asset('storage/' . $existing_company_logo) : '') }}" alt="Company logo preview" class="h-32 w-32 object-cover rounded-lg border-2 border-gray-300" />
                                @endif
                                <input wire:model="company_logo" type="file" accept="image/*" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" />
                                <x-input-error class="mt-2" :messages="$errors->get('company_logo')" />
                                <p class="mt-1 text-xs text-gray-500">Max 2MB. Recommended: 400x400px</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Cover Image -->
                <div>
                    <x-input-label :value="__('Cover Image (Optional)')" />
                    <div class="mt-2">
                        @if ($existing_cover_image || $cover_image)
                            <img src="{{ $cover_image ? $cover_image->temporaryUrl() : ($existing_cover_image ? asset('storage/' . $existing_cover_image) : '') }}" alt="Cover image preview" class="h-32 w-full object-cover rounded-lg border-2 border-gray-300" />
                        @endif
                        <input wire:model="cover_image" type="file" accept="image/*" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        <x-input-error class="mt-2" :messages="$errors->get('cover_image')" />
                        <p class="mt-1 text-xs text-gray-500">Max 5MB. Recommended: 1200x400px</p>
                    </div>
                </div>

                <!-- Personal Information Section -->
                @if($display_mode === 'personal_only' || $display_mode === 'combined')
                    <div class="p-6 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">👤 Personal Information</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="title" :value="__('Job Title / Position')" />
                                    <x-text-input wire:model="title" id="title" type="text" class="mt-1 block w-full" placeholder="e.g., Software Engineer, CEO" />
                                    <x-input-error class="mt-2" :messages="$errors->get('title')" />
                                </div>
                                <div>
                                    <x-input-label for="company" :value="__('Company (Employer)')" />
                                    <x-text-input wire:model="company" id="company" type="text" class="mt-1 block w-full" placeholder="e.g., Acme Corp" />
                                    <x-input-error class="mt-2" :messages="$errors->get('company')" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="personal_bio" :value="__('Personal Bio')" />
                                <textarea wire:model="personal_bio" id="personal_bio" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Tell us about yourself..."></textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('personal_bio')" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="website" :value="__('Personal Website')" />
                                    <x-text-input wire:model="website" id="website" type="url" class="mt-1 block w-full" placeholder="https://yourwebsite.com" />
                                    <x-input-error class="mt-2" :messages="$errors->get('website')" />
                                </div>
                                <div>
                                    <x-input-label for="address" :value="__('Address')" />
                                    <x-text-input wire:model="address" id="address" type="text" class="mt-1 block w-full" placeholder="City, Country" />
                                    <x-input-error class="mt-2" :messages="$errors->get('address')" />
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Company/Business Information Section -->
                @if($display_mode === 'company_only' || $display_mode === 'combined')
                    <div class="p-6 bg-green-50 border-l-4 border-green-500 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">🏢 Company Information</h3>
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="business_name" :value="__('Business/Company Name')" />
                                <x-text-input wire:model="business_name" id="business_name" type="text" class="mt-1 block w-full" placeholder="e.g., Acme Corporation" required />
                                <x-input-error class="mt-2" :messages="$errors->get('business_name')" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="tax_id" :value="__('Tax ID / Registration Number')" />
                                    <x-text-input wire:model="tax_id" id="tax_id" type="text" class="mt-1 block w-full" placeholder="VAT, TIN, EIN" />
                                    <x-input-error class="mt-2" :messages="$errors->get('tax_id')" />
                                </div>
                                <div>
                                    <x-input-label for="industry" :value="__('Industry')" />
                                    <x-text-input wire:model="industry" id="industry" type="text" class="mt-1 block w-full" placeholder="e.g., Technology, Healthcare" />
                                    <x-input-error class="mt-2" :messages="$errors->get('industry')" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="company_size" :value="__('Company Size')" />
                                <select wire:model="company_size" id="company_size" class="mt-1 block w-full border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm">
                                    <option value="">Select company size</option>
                                    <option value="1-10">1-10 employees</option>
                                    <option value="11-50">11-50 employees</option>
                                    <option value="51-200">51-200 employees</option>
                                    <option value="201-500">201-500 employees</option>
                                    <option value="501-1000">501-1000 employees</option>
                                    <option value="1000+">1000+ employees</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('company_size')" />
                            </div>

                            <div>
                                <x-input-label for="company_bio" :value="__('Company Bio / About')" />
                                <textarea wire:model="company_bio" id="company_bio" rows="4" class="mt-1 block w-full border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm" placeholder="Tell us about your company..."></textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('company_bio')" />
                            </div>

                            <div>
                                <x-input-label for="services_offered" :value="__('Services Offered')" />
                                <textarea wire:model="services_offered" id="services_offered" rows="3" class="mt-1 block w-full border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm" placeholder="List the main services or products you offer..."></textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('services_offered')" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="company_email" :value="__('Company Email')" />
                                    <x-text-input wire:model="company_email" id="company_email" type="email" class="mt-1 block w-full" placeholder="contact@company.com" />
                                    <x-input-error class="mt-2" :messages="$errors->get('company_email')" />
                                </div>
                                <div>
                                    <x-input-label for="company_phone" :value="__('Company Phone')" />
                                    <x-text-input wire:model="company_phone" id="company_phone" type="tel" class="mt-1 block w-full" placeholder="+1234567890" />
                                    <x-input-error class="mt-2" :messages="$errors->get('company_phone')" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="company_website" :value="__('Company Website')" />
                                    <x-text-input wire:model="company_website" id="company_website" type="url" class="mt-1 block w-full" placeholder="https://company.com" />
                                    <x-input-error class="mt-2" :messages="$errors->get('company_website')" />
                                </div>
                                <div>
                                    <x-input-label for="company_address" :value="__('Company Address')" />
                                    <x-text-input wire:model="company_address" id="company_address" type="text" class="mt-1 block w-full" placeholder="Street, City, Country" />
                                    <x-input-error class="mt-2" :messages="$errors->get('company_address')" />
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Contact Information (ProfileContact system) -->
                @if($current_profile_id)
                    <div class="p-6 bg-gray-50 border border-gray-300 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📞 Contact Numbers & Emails</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <livewire:profile.contact-manager :profileId="$current_profile_id" type="phone" :key="'phone-'.$current_profile_id" />
                            <livewire:profile.contact-manager :profileId="$current_profile_id" type="email" :key="'email-'.$current_profile_id" />
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">💡 Save your profile first to add multiple phone numbers and emails.</p>
                    </div>
                @endif

                <!-- Social Media URLs -->
                <div class="p-6 bg-purple-50 border-l-4 border-purple-500 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">🔗 Social Media Links</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="linkedin_url" :value="__('LinkedIn Profile')" />
                            <x-text-input wire:model="linkedin_url" id="linkedin_url" type="url" class="mt-1 block w-full" placeholder="https://linkedin.com/in/yourprofile" />
                            <x-input-error class="mt-2" :messages="$errors->get('linkedin_url')" />
                        </div>
                        <div>
                            <x-input-label for="facebook_url" :value="__('Facebook Profile')" />
                            <x-text-input wire:model="facebook_url" id="facebook_url" type="url" class="mt-1 block w-full" placeholder="https://facebook.com/yourpage" />
                            <x-input-error class="mt-2" :messages="$errors->get('facebook_url')" />
                        </div>
                        <div>
                            <x-input-label for="twitter_url" :value="__('Twitter/X Profile')" />
                            <x-text-input wire:model="twitter_url" id="twitter_url" type="url" class="mt-1 block w-full" placeholder="https://twitter.com/yourusername" />
                            <x-input-error class="mt-2" :messages="$errors->get('twitter_url')" />
                        </div>
                        <div>
                            <x-input-label for="instagram_url" :value="__('Instagram Profile')" />
                            <x-text-input wire:model="instagram_url" id="instagram_url" type="url" class="mt-1 block w-full" placeholder="https://instagram.com/yourusername" />
                            <x-input-error class="mt-2" :messages="$errors->get('instagram_url')" />
                        </div>
                    </div>
                </div>

                <!-- Additional Social Links -->
                <div class="p-6 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">🌐 Additional Links</h3>
                        <button type="button" wire:click="addSocialLink" class="px-3 py-1.5 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                            + Add Link
                        </button>
                    </div>

                    <div class="space-y-4">
                        @foreach ($socialLinks as $index => $link)
                            <div class="flex gap-4 items-start p-4 bg-white border border-gray-200 rounded-lg">
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <x-input-label :value="__('Platform')" />
                                        <select wire:model="socialLinks.{{ $index }}.platform" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                            <option value="custom">Custom</option>
                                            <option value="website">Website</option>
                                            <option value="portfolio">Portfolio</option>
                                            <option value="blog">Blog</option>
                                            <option value="youtube">YouTube</option>
                                            <option value="tiktok">TikTok</option>
                                            <option value="github">GitHub</option>
                                            <option value="medium">Medium</option>
                                            <option value="whatsapp">WhatsApp</option>
                                            <option value="telegram">Telegram</option>
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label :value="__('Label')" />
                                        <x-text-input wire:model="socialLinks.{{ $index }}.label" type="text" class="mt-1 block w-full text-sm" placeholder="e.g., My Portfolio" />
                                    </div>
                                    <div>
                                        <x-input-label :value="__('URL')" />
                                        <x-text-input wire:model="socialLinks.{{ $index }}.url" type="url" class="mt-1 block w-full text-sm" placeholder="https://" />
                                    </div>
                                </div>
                                <button type="button" wire:click="removeSocialLink({{ $index }})" class="mt-6 text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach

                        @if (empty($socialLinks))
                            <p class="text-sm text-gray-500 text-center py-4">No additional links added yet.</p>
                        @endif
                    </div>
                </div>

                <!-- Public Toggle -->
                <div class="flex items-center">
                    <input wire:model="is_public" id="is_public" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <x-input-label for="is_public" :value="__('Make profile publicly accessible')" class="ml-2" />
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <x-primary-button wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $isCreatingNew ? __('Create Profile') : __('Save Profile') }}</span>
                            <span wire:loading>Saving...</span>
                        </x-primary-button>
                        <x-action-message class="me-3" on="profile-saved">
                            {{ __('Saved.') }}
                        </x-action-message>
                    </div>

                    @if($current_profile_id && Auth::user()->profiles()->count() > 1)
                        <button type="button" wire:click="deleteProfile" wire:confirm="Are you sure you want to delete this profile? This action cannot be undone." class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100">
                            Delete Profile
                        </button>
                    @endif
                </div>
            </form>

            <!-- QR Code Section -->
            @if ($current_profile_id)
                @php
                    $currentProfile = Auth::user()->profiles()->find($current_profile_id);
                @endphp
                @if($currentProfile)
                    <div class="mt-8 p-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">📱 Your QR Code</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Share this QR code to let people quickly access your profile.
                        </p>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                            <div class="bg-white p-4 rounded-lg border-2 border-indigo-300 shadow-md">
                                <img src="{{ $currentProfile->qr_code_url }}" alt="QR Code" class="w-48 h-48">
                            </div>
                            <div class="flex-1">
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Profile URL:</p>
                                    <p class="text-sm text-gray-600 break-all font-mono bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                        {{ $currentProfile->public_url }}
                                    </p>
                                </div>
                                <a href="{{ $currentProfile->public_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </section>
</div>
