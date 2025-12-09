<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            
            // Link to the user who owns the cart
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Link to the product being held in the cart
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Quantity of the product
            $table->integer('quantity'); // Using standard integer

            // --- ADDED FOR VARIANT SUPPORT ---
            $table->string('size')->nullable(); 
            $table->string('color')->nullable(); 

            // CRITICAL FIX: The item is unique only if the user, product, size, AND color match.
            // This allows a user to have the same shoe in multiple sizes/colors.
            $table->unique(['user_id', 'product_id', 'size', 'color']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};