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
            $table->id(); // Primary Key

            $table->foreignId('role_id')
                  ->nullable()   // If you want to allow users with no specific role
                  ->constrained('roles')
                  ->nullOnDelete();

            $table->string('first_name'); // New field
            $table->string('last_name'); // New field
            $table->string('username')->nullable()->unique(); // Ensure username is unique
            $table->string('email')->nullable()->unique();
            $table->string('phone_number')->unique(); // New field
            // $table->string('role')->default('Student'); // Default role
            $table->enum('sex', ['male', 'female', 'other'])->nullable(); // New field
            $table->string('address')->nullable(); // New field
            $table->string('city')->nullable(); // New field
            $table->string('zip_code')->nullable(); // New field
            $table->string('profile_pic_url')->nullable(); // New field for profile picture

            $table->boolean('is_verified')->default(false);
            $table->boolean('suspended')->default(false); // New field for user status
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');
            $table->rememberToken();

            // Soft deletes in Laravel (replaces boolean `deleted`)
            $table->softDeletes(); // creates 'deleted_at' column

            $table->timestamps(); // created_at and updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
