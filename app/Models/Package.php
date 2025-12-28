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
        ];
    }

    /**
     * Get the pricing tiers for the package.
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
     * Calculate price for a given quantity.
     */
    public function getPriceForQuantity(int $quantity): ?float
    {
        // For NFC types, return base_price
        if (in_array($this->type, ['nfc_plain', 'nfc_printed'])) {
            return $this->base_price ? (float) $this->base_price : null;
        }

        // For Classic type, find matching tier
        if ($this->type === 'classic') {
            $tier = $this->activePricingTiers()
                ->where('min_quantity', '<=', $quantity)
                ->where(function ($query) use ($quantity) {
                    $query->whereNull('max_quantity')
                        ->orWhere('max_quantity', '>=', $quantity);
                })
                ->first();

            if ($tier) {
                return (float) ($tier->price_per_unit * $quantity);
            }
        }

        return null;
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
        if (in_array($this->type, ['nfc_plain', 'nfc_printed'])) {
            return $this->base_price !== null;
        }

        // Classic type needs at least one active tier
        if ($this->type === 'classic') {
            return $this->activePricingTiers()->exists();
        }

        return false;
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
