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
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                
                // Added these directly to the creation table
                $table->string('username')->unique();
                $table->text('address')->nullable();
                $table->string('phone')->nullable(); 
                
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                
                // Admin and Block flags
                $table->boolean('is_admin')->default(false);
                $table->boolean('is_blocked')->default(false); // <--- This is already here!
                
                $table->rememberToken();
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('users');
        }
    };