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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained()->onDelete('cascade');
            $table->string('qr_code')->unique();
            $table->enum('status', ['issued', 'cancelled', 'refunded', 'expired'])->default('issued');
            $table->enum('check_in_status', ['not_checked_in', 'checked_in'])->default('not_checked_in');
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users');
            $table->string('seat_number')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Add indexes for common query patterns
            $table->index(['booking_id', 'status']);
            $table->index(['ticket_type_id', 'status']);
            $table->index('check_in_status');
            $table->index('checked_in_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
