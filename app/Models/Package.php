<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'image',
        'is_active',
        'display_order',
        'features',
        'base_price',
        'subscription_renewal_price',
        'subscription_duration_days',
        'enable_multi_year_subscriptions',
        'printing_fee',
        'design_fee',
        'card_colors',
        'pricing_config',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'display_order' => 'integer',
            'features' => 'array',
            'base_price' => 'decimal:2',
            'subscription_renewal_price' => 'decimal:2',
            'subscription_duration_days' => 'integer',
            'enable_multi_year_subscriptions' => 'boolean',
            'printing_fee' => 'decimal:2',
            'design_fee' => 'decimal:2',
            'card_colors' => 'array',
            'pricing_config' => 'array',
        ];
    }

    /**
     * Get the pricing tiers for the package (for Classic cards - quantity-based).
     */
    public function pricingTiers(): HasMany
    {
        return $this->hasMany(PackagePricingTier::class)->orderBy('min_quantity');
    }

    /**
     * Get the active pricing tiers for the package.
     */
    public function activePricingTiers(): HasMany
    {
        return $this->pricingTiers()->where('is_active', true);
    }

    /**
     * Get the subscription tiers for the package (for multi-year subscriptions).
     */
    public function subscriptionTiers(): HasMany
    {
        return $this->hasMany(PackageSubscriptionTier::class)->ordered();
    }

    /**
     * Get the active subscription tiers for the package.
     */
    public function activeSubscriptionTiers(): HasMany
    {
        return $this->subscriptionTiers()->where('is_active', true);
    }

    /**
     * Get the orders for this package.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the package image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Calculate price for a given quantity with optional parameters.
     * 
     * @param int $quantity
     * @param array $options ['requires_printing' => bool, 'has_design' => bool, 'card_color' => string, 'subscription_years' => int]
     * @return array|null Returns pricing breakdown or null if calculation fails
     */
    public function calculatePrice(int $quantity = 1, array $options = []): ?array
    {
        $requiresPrinting = $options['requires_printing'] ?? false;
        $hasDesign = $options['has_design'] ?? false;
        $cardColor = $options['card_color'] ?? null;
        $subscriptionYears = $options['subscription_years'] ?? 1;

        // For NFC types (plain or printed)
        if ($this->type === 'nfc_card') {
            if (!$this->base_price) {
                return null;
            }

            $cardBasePrice = (float) $this->base_price; // 30,000 TZS (card + 1 year subscription)
            $printingFee = 0;
            $subscriptionPrice = $cardBasePrice; // Base price includes 1 year subscription
            $additionalYearsPrice = 0;
            $subscriptionDiscount = 0;

            // If printing is required, add printing fee
            if ($requiresPrinting && $this->printing_fee) {
                $printingFee = (float) $this->printing_fee; // 6,000 TZS
            }

            // Calculate additional subscription years if > 1
            if ($subscriptionYears > 1 && $this->enable_multi_year_subscriptions) {
                // Check if there's a subscription tier for this duration
                $tier = $this->activeSubscriptionTiers()
                    ->where('years', $subscriptionYears)
                    ->first();
                
                if ($tier) {
                    // Use tier pricing - tier price already includes the base price (card + 1st year)
                    // So we need to subtract base price to get just the additional years cost
                    $tierTotalPrice = (float) $tier->price;
                    $additionalYearsPrice = $tierTotalPrice - $cardBasePrice;
                    
                    // Calculate savings vs regular price
                    $regularAdditionalPrice = (float) ($this->subscription_renewal_price ?? 10000) * ($subscriptionYears - 1);
                    $subscriptionDiscount = $regularAdditionalPrice - $additionalYearsPrice;
                } else {
                    // Calculate based on renewal price with progressive discount
                    $renewalPrice = (float) ($this->subscription_renewal_price ?? 10000);
                    $additionalYears = $subscriptionYears - 1; // First year included in base
                    
                    // Apply progressive discount: Year 2 gets 5%, Year 3 gets 10%, etc.
                    $totalAdditionalPrice = 0;
                    $totalRegularPrice = 0;
                    
                    for ($i = 2; $i <= $subscriptionYears; $i++) {
                        $totalRegularPrice += $renewalPrice;
                        $discountPercentage = min(($i - 1) * 5, 15); // 5% for year 2, 10% for year 3, max 15%
                        $yearPrice = $renewalPrice * (1 - $discountPercentage / 100);
                        $totalAdditionalPrice += $yearPrice;
                    }
                    
                    $additionalYearsPrice = $totalAdditionalPrice;
                    $subscriptionDiscount = $totalRegularPrice - $totalAdditionalPrice;
                }
            }

            // Total price: card (with subscription) + printing fee + additional years
            $total = $cardBasePrice + $printingFee + $additionalYearsPrice;

            return [
                'base_price' => $cardBasePrice, // Card price (includes 1 year subscription)
                'subscription_price' => $subscriptionPrice, // 1 year subscription included in base
                'additional_years_price' => $additionalYearsPrice,
                'subscription_years' => $subscriptionYears,
                'subscription_discount' => $subscriptionDiscount,
                'printing_fee' => $printingFee,
                'design_fee' => 0,
                'unit_price' => $total, // Total unit price
                'total_price' => $total,
                'quantity' => 1, // NFC cards are always quantity 1
            ];
        }

        // For Classic type
        if ($this->type === 'classic') {
            $tier = $this->activePricingTiers()
                ->where('min_quantity', '<=', $quantity)
                ->where(function ($query) use ($quantity) {
                    $query->whereNull('max_quantity')
                        ->orWhere('max_quantity', '>=', $quantity);
                })
                ->first();

            if (!$tier) {
                return null;
            }

            $unitPrice = (float) $tier->price_per_unit;
            $basePrice = $unitPrice * $quantity;
            $designFee = 0;

            // If client doesn't have design, add design fee
            if (!$hasDesign && $this->design_fee) {
                $designFee = (float) $this->design_fee;
            }

            $total = $basePrice + $designFee;

            return [
                'base_price' => $basePrice,
                'subscription_price' => 0, // Classic cards don't have subscription
                'printing_fee' => 0, // Printing is included in base price for classic
                'design_fee' => $designFee,
                'unit_price' => $unitPrice,
                'total_price' => $total,
                'quantity' => $quantity,
            ];
        }

        return null;
    }

    /**
     * Calculate price for a given quantity (backward compatibility).
     * 
     * @deprecated Use calculatePrice() instead for flexible pricing
     */
    public function getPriceForQuantity(int $quantity): ?float
    {
        $pricing = $this->calculatePrice($quantity);
        return $pricing ? $pricing['total_price'] : null;
    }

    /**
     * Get available card colors for NFC packages.
     */
    public function getAvailableCardColors(): array
    {
        if ($this->type !== 'nfc_card') {
            return [];
        }

        return $this->card_colors ?? [];
    }

    /**
     * Check if package supports printing option.
     */
    public function supportsPrinting(): bool
    {
        return $this->type === 'nfc_card' && $this->printing_fee !== null;
    }

    /**
     * Check if package requires design option.
     */
    public function requiresDesignOption(): bool
    {
        return $this->type === 'classic' && $this->design_fee !== null;
    }

    /**
     * Check if package is available (active and has pricing).
     */
    public function isAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // NFC types need base_price
        if ($this->type === 'nfc_card') {
            return $this->base_price !== null;
        }

        // Classic type needs at least one active tier
        if ($this->type === 'classic') {
            return $this->activePricingTiers()->exists();
        }

        return false;
    }

    /**
     * Get available subscription duration options.
     * 
     * @return array Array of year options with pricing
     */
    public function getSubscriptionOptions(): array
    {
        if (!$this->enable_multi_year_subscriptions) {
            return [[
                'years' => 1,
                'price' => (float) $this->base_price,
                'label' => '1 Year',
                'savings' => 0,
                'savings_percentage' => 0,
            ]];
        }

        $options = [];
        $tiers = $this->activeSubscriptionTiers()->get();
        
        if ($tiers->isEmpty()) {
            // Generate default options dynamically (1-3 years)
            $renewalPrice = (float) ($this->subscription_renewal_price ?? 10000);
            $basePrice = (float) $this->base_price;
            
            for ($years = 1; $years <= 3; $years++) {
                $additionalYears = $years - 1;
                $baseAdditionalPrice = $renewalPrice * $additionalYears;
                
                // Apply discount: 10% for 3 years, 5% for 2 years
                $discountPercentage = $years >= 3 ? 10 : ($years >= 2 ? 5 : 0);
                $discount = $baseAdditionalPrice * ($discountPercentage / 100);
                $additionalPrice = $baseAdditionalPrice - $discount;
                $totalPrice = $basePrice + $additionalPrice;
                
                $label = $years === 3 ? 'Best Value' : ($years === 2 ? 'Save More' : null);
                
                $options[] = [
                    'years' => $years,
                    'price' => $totalPrice,
                    'label' => $label,
                    'savings' => $discount,
                    'savings_percentage' => $discountPercentage,
                    'price_per_year' => round($totalPrice / $years, 2),
                ];
            }
        } else {
            // Use defined tiers
            $basePrice = (float) $this->base_price;
            
            foreach ($tiers as $tier) {
                $totalPrice = $tier->years === 1 ? $basePrice : $basePrice + (float) $tier->price;
                
                $options[] = [
                    'years' => $tier->years,
                    'price' => $totalPrice,
                    'label' => $tier->label,
                    'savings' => $tier->savings,
                    'savings_percentage' => $tier->savings_percentage,
                    'price_per_year' => round($totalPrice / $tier->years, 2),
                ];
            }
        }
        
        return $options;
    }

    /**
     * Calculate renewal price for a given number of years.
     * For renewals, all years are charged at renewal price (no card cost)
     * 
     * @param int $years Number of years
     * @return array Pricing breakdown
     */
    public function calculateRenewalPrice(int $years = 1): array
    {
        $renewalPrice = (float) ($this->subscription_renewal_price ?? 10000);
        
        // For renewals, we don't use subscription tiers (those are for initial purchase)
        // Calculate based on renewal price with progressive discounts
        
        $regularPrice = $renewalPrice * $years;
        $totalPrice = 0;
        
        // Apply progressive discount per year
        for ($i = 1; $i <= $years; $i++) {
            // Year 1: no discount, Year 2: 5% off, Year 3: 10% off
            $discountPercentage = min(($i - 1) * 5, 15); // Max 15% discount
            $yearPrice = $renewalPrice * (1 - $discountPercentage / 100);
            $totalPrice += $yearPrice;
        }
        
        $savings = $regularPrice - $totalPrice;
        $savingsPercentage = $regularPrice > 0 ? round(($savings / $regularPrice) * 100, 2) : 0;
        
        $label = null;
        if ($years >= 3 && $savings > 0) {
            $label = 'Best Value - Save ' . number_format($savings) . ' TZS';
        } elseif ($years >= 2 && $savings > 0) {
            $label = 'Save ' . number_format($savings) . ' TZS';
        }
        
        return [
            'years' => $years,
            'price' => round($totalPrice, 2),
            'price_per_year' => round($totalPrice / $years, 2),
            'savings' => round($savings, 2),
            'savings_percentage' => $savingsPercentage,
            'label' => $label,
        ];
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
     * Scope a query to only include active packages.
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
        return $query->orderBy('display_order')->orderBy('created_at');
    }
}
