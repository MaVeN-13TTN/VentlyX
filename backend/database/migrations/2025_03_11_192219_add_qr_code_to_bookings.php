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
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('qr_code')->nullable();
            // Add transfer columns if they don't exist
            if (!Schema::hasColumn('bookings', 'transfer_status')) {
                $table->string('transfer_status')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'transfer_code')) {
                $table->string('transfer_code')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'transferred_at')) {
                $table->timestamp('transferred_at')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'transferred_to')) {
                $table->foreignId('transferred_to')->nullable()->references('id')->on('users');
            }
            if (!Schema::hasColumn('bookings', 'transferred_from')) {
                $table->foreignId('transferred_from')->nullable()->references('id')->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('qr_code');
            // Only drop columns if they exist
            if (Schema::hasColumn('bookings', 'transfer_status')) {
                $table->dropColumn('transfer_status');
            }
            if (Schema::hasColumn('bookings', 'transfer_code')) {
                $table->dropColumn('transfer_code');
            }
            if (Schema::hasColumn('bookings', 'transferred_at')) {
                $table->dropColumn('transferred_at');
            }
            if (Schema::hasColumn('bookings', 'transferred_to')) {
                $table->dropForeign(['transferred_to']);
                $table->dropColumn('transferred_to');
            }
            if (Schema::hasColumn('bookings', 'transferred_from')) {
                $table->dropForeign(['transferred_from']);
                $table->dropColumn('transferred_from');
            }
        });
    }
};
