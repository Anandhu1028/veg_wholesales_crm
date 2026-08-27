<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppAccount extends Model
{
    protected $table = 'whatsapp_accounts';

    protected $fillable = [
        'name',
        'phone_number',
        'provider',
        'status',
        'mode',
        'webhook_url',
        'api_key',
        'phone_number_id',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
