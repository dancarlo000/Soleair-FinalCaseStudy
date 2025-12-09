<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // Link to the parent order
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Link to the product being purchased
            $table->foreignId('product_id')->constrained()->onDelete('restrict'); 
            // Note: onDelete('restrict') is used here to prevent deleting a product that's tied to an existing order history.

            // Details recorded at the time of purchase
            $table->unsignedSmallInteger('quantity');
            $table->decimal('price', 10, 2); // Price at the time the order was placed (CRUCIAL)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};