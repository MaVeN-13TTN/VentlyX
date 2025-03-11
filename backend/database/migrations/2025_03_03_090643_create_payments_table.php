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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', ['stripe', 'paypal', 'mpesa', 'other'])->default('stripe');
            $table->string('payment_id')->nullable(); // External payment system ID
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->json('transaction_details')->nullable();
            $table->string('currency')->default('USD');
            $table->string('transaction_id')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('refund_date')->nullable();
            $table->string('refund_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
