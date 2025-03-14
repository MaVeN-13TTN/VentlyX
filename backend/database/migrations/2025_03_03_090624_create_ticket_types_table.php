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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Individual, Group, VIP, VVIP
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->text('description')->nullable();
            $table->integer('max_per_order')->nullable();
            $table->dateTime('sales_start_date')->nullable();
            $table->dateTime('sales_end_date')->nullable();
            $table->boolean('is_available')->default(true);
            $table->integer('tickets_remaining');
            $table->enum('status', ['draft', 'active', 'paused', 'sold_out', 'expired', 'cancelled'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
