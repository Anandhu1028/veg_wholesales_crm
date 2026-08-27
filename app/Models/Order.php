<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'conversation_id',
        'whatsapp_account_id',
        'source', // WhatsApp, Manual, Repeat Order
        'status', // New, Confirmed, Processing, Ready, Out for Delivery, Delivered, Cancelled
        'subtotal',
        'discount',
        'delivery_charge',
        'total_amount',
        'paid_amount',
        'pending_amount',
        'payment_status', // Unpaid, Pending, Partially Paid, Paid, Overdue
        'payment_method', // Pay Now, Cash on Delivery, Credit, COD, Cash, UPI, Bank Transfer, Card
        'delivery_address',
        'delivery_date',
        'time_slot',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'delivery_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function whatsappAccount(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date', 'desc');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate and synchronize paid/pending amounts and payment status
     */
    public function recalculatePaymentStatus(): void
    {
        $totalPaid = (float) $this->payments()->whereIn('payment_status', ['Paid', 'Completed'])->sum('amount');
        $totalAmount = (float) $this->total_amount;
        $pending = max(0.00, $totalAmount - $totalPaid);

        $status = 'Unpaid';
        if ($totalPaid >= $totalAmount && $totalAmount > 0) {
            $status = 'Paid';
        } elseif ($totalPaid > 0) {
            $status = 'Partially Paid';
        } elseif ($this->payment_method === 'Cash on Delivery' || $this->payment_method === 'COD' || $this->payment_method === 'Credit') {
            $status = 'Pending';
        }

        $this->update([
            'paid_amount' => $totalPaid,
            'pending_amount' => $pending,
            'payment_status' => $status,
        ]);
    }
}
