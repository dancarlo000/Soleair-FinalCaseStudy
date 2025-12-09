<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model 
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'total_amount', 
        'status', 
        'shipping_address', 
        'tracking_number', 
        'payment_id'
    ];

    // Cast total_amount to a decimal for correct handling
    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * An Order belongs to one User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * An Order has many OrderItems (the specific purchased items).
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the products contained in this order (convenience relation via OrderItem).
     */
    public function products(): BelongsToMany
    {
        // Links through the 'order_items' table and includes price/quantity from the pivot.
        return $this->belongsToMany(Product::class, 'order_items')
                    ->withPivot('quantity', 'price');
    }
}