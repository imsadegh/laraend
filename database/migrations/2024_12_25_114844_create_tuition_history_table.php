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
        Schema::create('tuition_history', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign keys
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();     // If user is deleted, remove payment history
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();     // If course is removed, remove or set this to null (your choice)

            // Payment & refund fields
            $table->decimal('amount_paid', 10, 2); // Payment amount
            $table->enum('payment_status', ['paid', 'pending', 'failed'])->default('pending'); // Payment status
            $table->timestamp('payment_date')->nullable(); // Date of payment
            $table->enum('payment_method', ['credit_card', 'bank_transfer', 'paypal', 'cash'])->default('credit_card'); // Payment method
            $table->string('transaction_id')->nullable(); // Optional transaction ID for payment gateway
            $table->json('payment_details')->nullable(); // JSON for storing additional details (e.g., gateway response)

            // Additional fields for refunds
            $table->timestamp('last_updated_status_at')->nullable(); // Timestamp of last status update
            $table->boolean('is_refundable')->default(false); // Indicates if payment is eligible for a refund
            $table->decimal('refund_amount', 10, 2)->nullable(); // Amount refunded, if applicable
            $table->enum('refund_status', ['not_requested', 'requested', 'processed', 'denied'])->default('not_requested'); // Refund status
            $table->string('receipt_url')->nullable(); // URL for payment receipt

            $table->softDeletes(); // adds 'deleted_at' column
            $table->timestamps(); // created_at and updated_at

            // Indexes for optimized querying
            $table->index('payment_status'); // Index for faster querying by payment status
            $table->index('payment_method'); // Index for payment method filtering
            $table->index('refund_status'); // Index for tracking refund status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuition_history');
    }
};
