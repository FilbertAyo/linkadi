<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NfcCard extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'profile_id',
        'order_id',
        'package_id',
        'card_number',
        'qr_code',
        'card_color',
        'requires_printing',
        'printing_text',
        'design_file',
        'activated_at',
        'expires_at',
        'status',
        'production_notes',
        'tracking_number',
        'shipped_at',
        'delivered_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_printing' => 'boolean',
            'printing_text' => 'array',
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the NFC card.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the profile linked to this NFC card.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the order for this NFC card.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the package for this NFC card.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Generate a unique card number.
     */
    public static function generateCardNumber(): string
    {
        do {
            $number = 'NFC' . now()->format('Ymd') . strtoupper(Str::random(8));
        } while (static::where('card_number', $number)->exists());

        return $number;
    }

    /**
     * Check if card is pending production.
     */
    public function isPendingProduction(): bool
    {
        return $this->status === 'pending_production';
    }

    /**
     * Check if card is in production.
     */
    public function isInProduction(): bool
    {
        return $this->status === 'in_production';
    }

    /**
     * Check if card is produced and ready to ship.
     */
    public function isProduced(): bool
    {
        return $this->status === 'produced';
    }

    /**
     * Check if card has been shipped.
     */
    public function isShipped(): bool
    {
        return $this->status === 'shipped';
    }

    /**
     * Check if card has been delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if card is activated.
     */
    public function isActivated(): bool
    {
        return $this->status === 'activated';
    }

    /**
     * Check if card subscription has expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
            ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if card is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if card is deactivated.
     */
    public function isDeactivated(): bool
    {
        return $this->status === 'deactivated';
    }

    /**
     * Check if card subscription is expiring soon (within 30 days).
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
     * Activate the card.
     */
    public function activate(int $subscriptionMonths = 12): void
    {
        $this->activated_at = $this->activated_at ?? now();
        $this->expires_at = now()->addMonths($subscriptionMonths);
        $this->status = 'activated';
        $this->save();
    }

    /**
     * Mark card as in production.
     */
    public function startProduction(): void
    {
        $this->status = 'in_production';
        $this->save();
    }

    /**
     * Mark card as produced.
     */
    public function markAsProduced(): void
    {
        $this->status = 'produced';
        $this->save();
    }

    /**
     * Mark card as shipped.
     */
    public function ship(string $trackingNumber): void
    {
        $this->status = 'shipped';
        $this->tracking_number = $trackingNumber;
        $this->shipped_at = now();
        $this->save();
    }

    /**
     * Mark card as delivered.
     */
    public function markAsDelivered(): void
    {
        $this->status = 'delivered';
        $this->delivered_at = now();
        $this->save();
    }

    /**
     * Suspend the card.
     */
    public function suspend(string $reason = null): void
    {
        $this->status = 'suspended';
        if ($reason) {
            $this->production_notes = ($this->production_notes ?? '') . "\nSuspended: " . $reason;
        }
        $this->save();
    }

    /**
     * Deactivate the card.
     */
    public function deactivate(string $reason = null): void
    {
        $this->status = 'deactivated';
        if ($reason) {
            $this->production_notes = ($this->production_notes ?? '') . "\nDeactivated: " . $reason;
        }
        $this->save();
    }

    /**
     * Renew card subscription.
     */
    public function renew(int $months = 12): void
    {
        $startDate = $this->expires_at && $this->expires_at->isFuture()
            ? $this->expires_at
            : now();

        $this->expires_at = $startDate->addMonths($months);
        
        // Reactivate if expired
        if ($this->status === 'expired') {
            $this->status = 'activated';
        }
        
        $this->save();
    }

    /**
     * Get the profile URL that this card links to.
     */
    public function getProfileUrlAttribute(): string
    {
        return $this->profile ? $this->profile->public_url : '';
    }

    /**
     * Get the status badge color for display.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending_production' => 'yellow',
            'in_production' => 'blue',
            'produced' => 'purple',
            'shipped' => 'indigo',
            'delivered' => 'green',
            'activated' => 'emerald',
            'expired' => 'red',
            'suspended' => 'orange',
            'deactivated' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pending_production' => 'Pending Production',
            'in_production' => 'In Production',
            'produced' => 'Produced',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'activated' => 'Activated',
            'expired' => 'Expired',
            'suspended' => 'Suspended',
            'deactivated' => 'Deactivated',
            default => ucfirst($this->status),
        };
    }

    /**
     * Scope a query to only include cards pending production.
     */
    public function scopePendingProduction($query)
    {
        return $query->where('status', 'pending_production');
    }

    /**
     * Scope a query to only include cards in production.
     */
    public function scopeInProduction($query)
    {
        return $query->where('status', 'in_production');
    }

    /**
     * Scope a query to only include produced cards.
     */
    public function scopeProduced($query)
    {
        return $query->where('status', 'produced');
    }

    /**
     * Scope a query to only include shipped cards.
     */
    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    /**
     * Scope a query to only include activated cards.
     */
    public function scopeActivated($query)
    {
        return $query->where('status', 'activated');
    }

    /**
     * Scope a query to only include active cards (activated and not expired).
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'activated')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope a query to only include expired cards.
     */
    public function scopeExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'expired')
                ->orWhere(function ($query) {
                    $query->where('status', 'activated')
                        ->whereNotNull('expires_at')
                        ->where('expires_at', '<=', now());
                });
        });
    }
}
