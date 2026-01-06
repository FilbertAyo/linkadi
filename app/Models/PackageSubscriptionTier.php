<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageSubscriptionTier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'package_id',
        'years',
        'price',
        'discount_percentage',
        'label',
        'is_active',
        'display_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'years' => 'integer',
            'price' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    /**
     * Get the package that owns the subscription tier.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Calculate the price per year for this tier.
     */
    public function getPricePerYearAttribute(): float
    {
        return (float) ($this->price / $this->years);
    }

    /**
     * Calculate the total savings compared to yearly renewal.
     */
    public function getSavingsAttribute(): float
    {
        $package = $this->package;
        if (!$package || !$package->subscription_renewal_price) {
            return 0;
        }

        $regularPrice = (float) $package->subscription_renewal_price * $this->years;
        return $regularPrice - (float) $this->price;
    }

    /**
     * Get the savings percentage.
     */
    public function getSavingsPercentageAttribute(): float
    {
        $package = $this->package;
        if (!$package || !$package->subscription_renewal_price) {
            return 0;
        }

        $regularPrice = (float) $package->subscription_renewal_price * $this->years;
        if ($regularPrice == 0) {
            return 0;
        }

        return round((($regularPrice - (float) $this->price) / $regularPrice) * 100, 2);
    }

    /**
     * Scope a query to only include active tiers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('years');
    }
}
