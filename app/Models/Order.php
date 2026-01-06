<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'package_id',
        'profile_id',
        'quantity',
        'unit_price',
        'base_price',
        'subscription_price',
        'subscription_years',
        'subscription_discount',
        'printing_fee',
        'design_fee',
        'total_price',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'shipping_address',
        'notes',
        'requires_printing',
        'has_design',
        'card_color',
        'pricing_breakdown',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'base_price' => 'decimal:2',
            'subscription_price' => 'decimal:2',
            'subscription_years' => 'integer',
            'subscription_discount' => 'decimal:2',
            'printing_fee' => 'decimal:2',
            'design_fee' => 'decimal:2',
            'total_price' => 'decimal:2',
            'status' => 'string',
            'payment_status' => 'string',
            'paid_at' => 'datetime',
            'requires_printing' => 'boolean',
            'has_design' => 'boolean',
            'pricing_breakdown' => 'array',
        ];
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the package for this order.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the NFC cards for this order.
     */
    public function nfcCards(): HasMany
    {
        return $this->hasMany(NfcCard::class);
    }

    /**
     * Check if order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if order is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if order is shipped.
     */
    public function isShipped(): bool
    {
        return $this->status === 'shipped';
    }

    /**
     * Check if order is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Check if order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if payment is pending.
     */
    public function isPaymentPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Check if payment is completed.
     */
    public function isPaymentPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if payment failed.
     */
    public function isPaymentFailed(): bool
    {
        return $this->payment_status === 'failed';
    }

    /**
     * Check if payment was refunded.
     */
    public function isPaymentRefunded(): bool
    {
        return $this->payment_status === 'refunded';
    }

    /**
     * Check if payment was cancelled.
     */
    public function isPaymentCancelled(): bool
    {
        return $this->payment_status === 'cancelled';
    }

    /**
     * Get the payment status badge color.
     */
    public function getPaymentStatusBadgeColorAttribute(): string
    {
        return match($this->payment_status) {
            'pending' => 'yellow',
            'paid' => 'green',
            'failed' => 'red',
            'refunded' => 'orange',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get the payment status display name.
     */
    public function getPaymentStatusDisplayAttribute(): string
    {
        return match($this->payment_status) {
            'pending' => 'Pending Payment',
            'paid' => 'Paid',
            'failed' => 'Payment Failed',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->payment_status),
        };
    }

    /**
     * Scope a query to only include paid orders.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include pending payment orders.
     */
    public function scopePendingPayment($query)
    {
        return $query->where('payment_status', 'pending');
    }
}
