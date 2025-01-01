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
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')
                  ->constrained('roles')
                  ->onDelete('cascade');   // If a role is deleted, remove pivot records
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');  // If a user is deleted, remove pivot records

            // Optional: track timestamps for when the role was assigned
            $table->timestamps();

            // Unique constraint to avoid duplicating the same user/role entry
            $table->unique(['role_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
