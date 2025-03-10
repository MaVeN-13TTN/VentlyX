<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('transfer_code', 8)->nullable()->unique();
            $table->enum('transfer_status', ['pending', 'completed', 'cancelled'])->nullable();
            $table->timestamp('transfer_initiated_at')->nullable();
            $table->timestamp('transfer_completed_at')->nullable();

            // Add index for transfer code lookups
            $table->index('transfer_code');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'transfer_code',
                'transfer_status',
                'transfer_initiated_at',
                'transfer_completed_at'
            ]);
        });
    }
};
