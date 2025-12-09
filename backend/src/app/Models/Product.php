<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str; // <--- Import this for slug generation

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'brand',
        'category',
        'price',
        'quantity',
        'discount', // <--- ADDED THIS for Feature 3
        'image',
        'description',
        'slug',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'discount' => 'integer', // <--- ADDED THIS
        'is_active' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     * Automatically runs when a product is being created.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            // Auto-generate slug if missing
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(5);
            }

            // Provide default description if missing (prevents DB errors)
            if (empty($product->description)) {
                $product->description = $product->name . ' by ' . $product->brand;
            }

            // Default active status
            if (is_null($product->is_active)) {
                $product->is_active = true;
            }
            
            // Default discount if missing (Feature 3)
            if (is_null($product->discount)) {
                $product->discount = 0;
            }
        });
    }

    // -------------------------------------------------------------------
    // E-COMMERCE RELATIONSHIPS
    // -------------------------------------------------------------------

    // Connection to Orders (Pivot table: order_items)
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
                    ->withPivot('quantity', 'price');
    }

    // Connection to Carts (One Product can be in many Users' carts)
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}