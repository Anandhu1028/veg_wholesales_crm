<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'whatsapp_number',
        'email',
        'address',
        'payment_terms',
        'outstanding_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'outstanding_balance' => 'decimal:2',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class)->orderBy('order_date', 'desc');
    }
}
