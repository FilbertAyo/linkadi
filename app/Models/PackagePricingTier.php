<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagePricingTier extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'package_id',
        'min_quantity',
        'max_quantity',
        'price_per_unit',
        'total_price',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'price_per_unit' => 'decimal:2',
            'total_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the package that owns the pricing tier.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Check if quantity falls within this tier's range.
     */
    public function matchesQuantity(int $quantity): bool
    {
        if ($quantity < $this->min_quantity) {
            return false;
        }

        if ($this->max_quantity === null) {
            return true; // Unlimited upper bound
        }

        return $quantity <= $this->max_quantity;
    }

    /**
     * Calculate price for given quantity within this tier.
     */
    public function calculatePrice(int $quantity): float
    {
        if ($this->total_price !== null) {
            return (float) $this->total_price;
        }

        return (float) ($this->price_per_unit * $quantity);
    }

    /**
     * Scope a query to only include active tiers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to find tier matching quantity.
     */
    public function scopeForQuantity($query, int $quantity)
    {
        return $query->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')
                    ->orWhere('max_quantity', '>=', $quantity);
            });
    }
}
