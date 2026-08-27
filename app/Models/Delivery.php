<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = [
        'order_id',
        'driver_name',
        'driver_phone',
        'vehicle_number',
        'delivery_date',
        'time_slot',
        'status', // Pending, Preparing, Ready, Out for Delivery, Delivered, Failed
        'delivery_notes',
        'delivered_at',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'delivered_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
