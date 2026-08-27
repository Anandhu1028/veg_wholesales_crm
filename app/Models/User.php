<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // admin, order_staff, accounts, delivery_staff
        'phone',
        'status',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOrderStaff(): bool
    {
        return in_array($this->role, ['admin', 'order_staff']);
    }

    public function isAccounts(): bool
    {
        return in_array($this->role, ['admin', 'accounts']);
    }

    public function isDeliveryStaff(): bool
    {
        return in_array($this->role, ['admin', 'delivery_staff']);
    }
}
