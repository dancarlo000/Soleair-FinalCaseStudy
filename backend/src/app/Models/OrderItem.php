<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model 
{
    use HasFactory;

    // Order items use standard timestamps, but they are a pivot model
    // and don't typically have their own mass-fillable properties other than foreign keys/data.
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price'];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // -------------------------------------------------------------------
    // RELATIONSHIPS
    // -------------------------------------------------------------------

    /**
     * An OrderItem belongs to one Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * An OrderItem belongs to one Product (the product that was purchased).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}