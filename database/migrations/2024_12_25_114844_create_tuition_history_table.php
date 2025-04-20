<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tuition_history', function (Blueprint $table) {
            $table->id(); // Primary Key

            // Foreign keys
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');     // If user is deleted, remove payment history
            $table->foreignId('course_id')->constrained('courses')->nullOnDelete();     // If course is removed, set this to null

            // Payment & refund fields
            $table->decimal('amount_paid', 10, 0);
            $table->integer('installment_number')->default(1)->comment('Installment number for payment tracking');
            $table->enum('payment_status', ['paid', 'pending', 'failed', 'refunded'])->default('pending');
            $table->timestamp('payment_date')->nullable();
            $table->enum('payment_method', ['credit_card', 'bank_transfer', 'zarinpal', 'cash'])->default('zarinpal');
            $table->string('payment_provider')->nullable(); // Optionally track the provider (e.g., Stripe, zarinpal)
            $table->string('payment_reference')->nullable();
            $table->string('payment_receipt')->nullable(); // URL for payment receipt
            $table->string('payment_description')->nullable();
            $table->string('payment_status_message')->nullable(); // Message from payment gateway
            $table->string('payment_failure_reason')->nullable();
            $table->string('transaction_id')->nullable(); // Optional transaction ID for payment gateway
            $table->json('payment_details')->nullable(); // JSON for storing additional details (e.g., gateway response)
            $table->timestamp('last_updated_status_at')->nullable();
            $table->boolean('is_refundable')->default(false);
            $table->decimal('refund_amount', 10, 0)->nullable(); // Amount refunded
            $table->enum('refund_status', ['not_requested', 'requested', 'processed', 'denied'])->default('not_requested');
            $table->timestamp('last_refunded_at')->nullable(); // Timestamp when a refund is processed

            $table->timestamps();
            $table->softDeletes();

            // Indexes for optimized querying
            $table->index('user_id');
            $table->index('payment_status');
            $table->index('payment_receipt');
            $table->index('refund_status');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tuition_history');
    }
};
