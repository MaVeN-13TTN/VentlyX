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
            $table->decimal('subtotal', 10, 2)->after('total_price')->nullable();
            $table->decimal('discount_amount', 10, 2)->after('subtotal')->default(0);
            $table->foreignId('discount_code_id')->nullable()->after('discount_amount')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['discount_code_id']);
            $table->dropColumn(['subtotal', 'discount_amount', 'discount_code_id']);
        });
    }
};