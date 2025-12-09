<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Link to the user who placed the order
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Order Totals
            // Stores the total amount charged for the order
            $table->decimal('total_amount', 10, 2); 

            // Order Status (e.g., pending, processing, shipped, completed, cancelled)
            $table->string('status')->default('pending');
            
            // Shipping Details
            $table->text('shipping_address');
            $table->string('tracking_number')->nullable();
            
            // Optionally, store payment confirmation details (e.g., payment gateway transaction ID)
            $table->string('payment_id')->nullable()->unique();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};