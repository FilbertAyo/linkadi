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
        'profile_type',
        'slug',
        'title',
        'company',
        'business_name',
        'tax_id',
        'bio',
        'phone',
        'email',
        'website',
        'address',
        'profile_image',
        'cover_image',
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
     */
    public static function generateUniqueSlug(string $base): string
    {
        $slug = Str::slug($base);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
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
        return $this->isPublished() && $this->is_public && !$this->isExpired() && !$this->isSuspended();
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'ready' => 'blue',
            'pending_payment' => 'yellow',
            'paid' => 'green',
            'published' => 'emerald',
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
     * Get the display name based on profile type.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->isBusiness() && $this->business_name) {
            return $this->business_name;
        }
        
        return $this->user->name;
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
}
