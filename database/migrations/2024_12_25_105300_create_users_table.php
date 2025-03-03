<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign key to roles table
            $table->foreignId('role_id')->constrained('roles')->nullOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('full_name');
            $table->string('username')->nullable()->unique(); // Ensure username is unique
            $table->string('email')->nullable()->unique();
            $table->string('phone_number')->unique();
            $table->string('melli_code')->nullable()->unique();

            // $table->string('role')->default('Student'); // Default role
            $table->enum('sex', ['male', 'female', 'other'])->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('avatar')->nullable(); // for profile picture

            $table->boolean('is_verified')->default(false);
            $table->boolean('suspended')->default(false);
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');
            $table->rememberToken();

            $table->softDeletes(); // creates 'deleted_at' column
            $table->timestamps(); // created_at and updated_at

            // Indexes for optimized querying
            $table->index('username');
            $table->index('phone_number');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('phone_number')->primary();
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


    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
