<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Profile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'profile_name',
        'display_mode',
        'is_primary',
        'profile_type',
        'slug',
        'title',
        'company',
        'business_name',
        'tax_id',
        'bio',
        'personal_bio',
        'company_bio',
        'phone',
        'email',
        'company_phone',
        'company_email',
        'website',
        'company_website',
        'address',
        'company_address',
        'profile_image',
        'company_logo',
        'cover_image',
        'industry',
        'company_size',
        'services_offered',
        'linkedin_url',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'is_public',
        'status',
        'published_at',
        'expires_at',
        'package_id',
        'order_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_primary' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the social links for the profile.
     */
    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class)->orderBy('order');
    }

    /**
     * Get the contacts for the profile.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(ProfileContact::class)->orderBy('is_primary', 'desc')->orderBy('order');
    }

    /**
     * Get phone contacts for the profile.
     */
    public function phones(): HasMany
    {
        return $this->hasMany(ProfileContact::class)->phones();
    }

    /**
     * Get email contacts for the profile.
     */
    public function emails(): HasMany
    {
        return $this->hasMany(ProfileContact::class)->emails();
    }

    /**
     * Get the primary phone contact.
     */
    public function getPrimaryPhoneAttribute(): ?ProfileContact
    {
        return $this->phones()->where('is_primary', true)->first();
    }

    /**
     * Get the primary email contact.
     */
    public function getPrimaryEmailAttribute(): ?ProfileContact
    {
        return $this->emails()->where('is_primary', true)->first();
    }

    /**
     * Get the package associated with the profile.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the order associated with the profile.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the NFC cards linked to this profile.
     */
    public function nfcCards(): HasMany
    {
        return $this->hasMany(NfcCard::class);
    }

    /**
     * Get the active NFC card for this profile.
     */
    public function activeNfcCard(): HasMany
    {
        return $this->hasMany(NfcCard::class)
            ->whereIn('status', ['activated', 'delivered', 'shipped']);
    }

    /**
     * Get the profile image URL.
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }

        return asset('storage/' . $this->profile_image);
    }

    /**
     * Get the cover image URL.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }

        return asset('storage/' . $this->cover_image);
    }

    /**
     * Generate a unique slug from a given string.
     * Slugs are globally unique across all profiles.
     */
    public static function generateUniqueSlug(string $base, ?int $excludeProfileId = null): string
    {
        $slug = Str::slug($base);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::query();
        
        // If profileId is provided, exclude it from uniqueness check (for updates)
        if ($excludeProfileId) {
            $query->where('id', '!=', $excludeProfileId);
        }

        while ($query->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the public profile URL.
     */
    public function getPublicUrlAttribute(): string
    {
        return url('/p/' . $this->slug);
    }

    /**
     * Get the QR code image URL.
     */
    public function getQrCodeUrlAttribute(?int $size = 200): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($this->public_url);
    }

    /**
     * Check if the profile is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the profile is ready (completed but not paid).
     */
    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    /**
     * Check if the profile is pending payment.
     */
    public function isPendingPayment(): bool
    {
        return $this->status === 'pending_payment';
    }

    /**
     * Check if the profile is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if the profile is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if the profile is active (published).
     */
    public function isActive(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if the profile is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Check if the profile is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if the profile can be published.
     */
    public function canPublish(): bool
    {
        return $this->status === 'paid' && !$this->isSuspended();
    }

    /**
     * Activate profile with subscription.
     */
    public function activate(int $subscriptionMonths = 12): void
    {
        $this->status = 'published';
        $this->published_at = $this->published_at ?? now();
        $this->expires_at = now()->addMonths($subscriptionMonths);
        $this->save();
    }

    /**
     * Check if subscription is expiring soon (30 days).
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isFuture() 
            && $this->expires_at->diffInDays(now()) <= 30;
    }

    /**
     * Renew subscription.
     */
    public function renew(int $months = 12): void
    {
        $startDate = $this->expires_at && $this->expires_at->isFuture()
            ? $this->expires_at
            : now();
            
        $this->expires_at = $startDate->addMonths($months);
        $this->status = 'published';
        $this->save();
    }

    /**
     * Check if the profile can be edited.
     */
    public function canEdit(): bool
    {
        return !$this->isSuspended();
    }

    /**
     * Check if the profile is publicly accessible.
     */
    public function isPubliclyAccessible(): bool
    {
        // Must be published (not draft, ready, pending_payment, paid, expired, or suspended)
        if (!$this->isPublished()) {
            return false;
        }

        // Must have is_public flag set to true
        if (!$this->is_public) {
            return false;
        }

        // Must not be expired or suspended
        if ($this->isExpired() || $this->isSuspended()) {
            return false;
        }

        // If there's an expiration date, it must be in the future
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // If there's an order_id, the order must be paid
        if ($this->order_id) {
            // Load order if not already loaded
            if (!$this->relationLoaded('order')) {
                $this->load('order');
            }
            
            if ($this->order && !$this->order->isPaymentPaid()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if profile can go public.
     */
    public function canBePublic(): bool
    {
        return in_array($this->status, ['active', 'published'])
            && (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'ready' => 'indigo',
            'pending_payment' => 'yellow',
            'paid' => 'green',
            'published' => 'emerald',
            'active' => 'emerald',
            'expired' => 'red',
            'suspended' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'ready' => 'Ready',
            'pending_payment' => 'Pending Payment',
            'paid' => 'Paid',
            'published' => 'Published',
            'active' => 'Active',
            'expired' => 'Expired',
            'suspended' => 'Suspended',
            default => ucfirst($this->status),
        };
    }

    /**
     * Scope a query to only include published profiles.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('is_public', true);
    }

    /**
     * Scope a query to only include active profiles (published and not expired/suspended).
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'published')
            ->where('is_public', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope a query to only include expired profiles.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Check if profile is individual type.
     */
    public function isIndividual(): bool
    {
        return $this->profile_type === 'individual';
    }

    /**
     * Check if profile is business type.
     */
    public function isBusiness(): bool
    {
        return $this->profile_type === 'business';
    }

    /**
     * Check if profile is business type (alias for isBusiness).
     */
    public function isBusinessProfile(): bool
    {
        return $this->isBusiness();
    }

    /**
     * Get the subtitle based on profile type.
     */
    public function getSubtitleAttribute(): ?string
    {
        if ($this->isBusiness()) {
            // For business: show contact person if available
            if ($this->title) {
                return $this->title . ($this->user->name ? ' - ' . $this->user->name : '');
            }
            return $this->user->name;
        }
        
        // For individual: show title at company
        if ($this->title && $this->company) {
            return $this->title . ' at ' . $this->company;
        }
        
        return $this->title ?: $this->company;
    }

    /**
     * Scope a query to only include individual profiles.
     */
    public function scopeIndividual($query)
    {
        return $query->where('profile_type', 'individual');
    }

    /**
     * Scope a query to only include business profiles.
     */
    public function scopeBusiness($query)
    {
        return $query->where('profile_type', 'business');
    }

    /**
     * Check if profile displays personal information only.
     */
    public function displaysPersonalOnly(): bool
    {
        return $this->display_mode === 'personal_only';
    }

    /**
     * Check if profile displays company information only.
     */
    public function displaysCompanyOnly(): bool
    {
        return $this->display_mode === 'company_only';
    }

    /**
     * Check if profile displays combined information.
     */
    public function displaysCombined(): bool
    {
        return $this->display_mode === 'combined';
    }

    /**
     * Get display name for the profile (uses profile_name, falls back to business_name or user name).
     */
    public function getDisplayNameAttribute(): string
    {
        // First priority: profile_name (user-friendly name)
        if ($this->profile_name) {
            return $this->profile_name;
        }
        
        // Second priority: business name for business profiles
        if ($this->isBusiness() && $this->business_name) {
            return $this->business_name;
        }
        
        // Fallback: user name with profile type
        return $this->user->name . ' - ' . ucfirst($this->profile_type ?? 'individual');
    }

    /**
     * Scope a query to only include primary profiles.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Set this profile as primary and unset others for the same user.
     */
    public function setAsPrimary(): void
    {
        // Unset other primary profiles for this user
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);
        
        // Set this as primary
        $this->update(['is_primary' => true]);
    }
}
