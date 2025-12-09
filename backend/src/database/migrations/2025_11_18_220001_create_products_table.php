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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // 1. Core Product Information
            $table->string('name');
            $table->string('brand');    
            $table->string('category'); 
            
            // 2. Details
            $table->string('slug')->nullable(); 
            $table->text('description')->nullable();

            // 3. Pricing and Stock
            $table->decimal('price', 10, 2); 
            $table->integer('quantity'); 
            
            // --- NEW FIELD: Discount Percentage ---
            $table->integer('discount')->default(0); 

            // 4. Media
            $table->longText('image');   
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};