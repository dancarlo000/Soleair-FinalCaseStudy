<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',      // For login
        'email',
        'password',
        'address',       // For shipping/billing
        'phone',  // For contact
        'is_admin',      // For role management
        'is_blocked',    
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_blocked' => 'boolean', 
        ];
    }

    // -------------------------------------------------------------------
    // E-COMMERCE RELATIONSHIPS
    // -------------------------------------------------------------------

    /**
     * Get the orders associated with the user.
     * A User can have many Orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the cart items associated with the user.
     * A User can have many Cart records (their current shopping cart).
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}