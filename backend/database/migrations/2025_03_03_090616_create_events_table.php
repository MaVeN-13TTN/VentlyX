<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->string('venue');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('image_url')->nullable();
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->integer('capacity')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'postponed', 'ended', 'archived'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->json('additional_info')->nullable();
            $table->timestamps();

            // Add indexes for commonly searched/filtered fields
            $table->index('status');
            $table->index('featured');
            $table->index('start_time');
            $table->index('end_time');
            $table->index('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
