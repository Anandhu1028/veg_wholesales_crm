<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'customer_id',
        'whatsapp_account_id',
        'user_id',
        'status', // bot_active, human_required, closed
        'bot_state', // START, WELCOME, ORDER_SELECTION, COLLECT_ORDER, CONFIRM_ORDER, ORDER_CREATED, HUMAN_HANDOFF, COMPLETED
        'bot_context',
        'last_message',
        'last_message_at',
        'unread_count',
        'is_starred',
    ];

    protected $casts = [
        'bot_context' => 'array',
        'last_message_at' => 'datetime',
        'is_starred' => 'boolean',
        'unread_count' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function whatsappAccount(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }
}
