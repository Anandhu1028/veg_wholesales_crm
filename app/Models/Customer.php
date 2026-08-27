<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'business_name',
        'phone',
        'whatsapp_number',
        'email',
        'business_type',
        'address',
        'city',
        'credit_limit',
        'credit_enabled',
        'outstanding_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_enabled' => 'boolean',
        'outstanding_balance' => 'decimal:2',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }

    public function latestOrder(): HasOne
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date', 'desc');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function customPrices(): HasMany
    {
        return $this->hasMany(CustomerProductPrice::class);
    }

    /**
     * Get price for product considering custom pricing priority
     */
    public function getProductPrice(Product $product): float
    {
        $customPrice = $this->customPrices()->where('product_id', $product->id)->first();
        if ($customPrice && $customPrice->custom_price > 0) {
            return (float) $customPrice->custom_price;
        }
        return (float) $product->base_price;
    }

    public function getAvailableCreditAttribute(): float
    {
        if (!$this->credit_enabled) {
            return 0.00;
        }
        return max(0.00, (float)$this->credit_limit - (float)$this->outstanding_balance);
    }

    public function isCreditEligibleFor(float $amount): bool
    {
        return $this->credit_enabled && ($this->available_credit >= $amount);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->business_name ?: $this->name;
    }

    public function getInitialsAttribute(): string
    {
        $name = $this->displayName;
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
}
