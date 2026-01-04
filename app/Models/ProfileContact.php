<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileContact extends Model
{
    protected $fillable = [
        'profile_id',
        'type',
        'value',
        'category',
        'custom_label',
        'is_primary',
        'order',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_public' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * Get the profile that owns the contact.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the display label for this contact.
     */
    public function getDisplayLabelAttribute(): string
    {
        if ($this->category === 'custom' && $this->custom_label) {
            return $this->custom_label;
        }
        
        return ucfirst($this->category);
    }

    /**
     * Get the icon for this contact type and category.
     */
    public function getIconAttribute(): string
    {
        if ($this->type === 'phone') {
            return match($this->category) {
                'mobile' => '📱',
                'work' => '☎️',
                'home' => '🏠',
                default => '📞',
            };
        }
        
        return match($this->category) {
            'work' => '💼',
            'home' => '🏠',
            default => '✉️',
        };
    }

    /**
     * Get the vCard type for this contact.
     */
    public function getVcardTypeAttribute(): string
    {
        if ($this->type === 'phone') {
            return match($this->category) {
                'mobile' => 'CELL',
                'work' => 'WORK',
                'home' => 'HOME',
                'main' => 'MAIN',
                default => 'VOICE',
            };
        }
        
        return match($this->category) {
            'work' => 'WORK',
            'home' => 'HOME',
            'main' => 'PREF,INTERNET',
            default => 'INTERNET',
        };
    }

    /**
     * Scope to only include phone contacts.
     */
    public function scopePhones($query)
    {
        return $query->where('type', 'phone')->orderBy('is_primary', 'desc')->orderBy('order');
    }

    /**
     * Scope to only include email contacts.
     */
    public function scopeEmails($query)
    {
        return $query->where('type', 'email')->orderBy('is_primary', 'desc')->orderBy('order');
    }

    /**
     * Scope to only include primary contacts.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to only include public contacts.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
