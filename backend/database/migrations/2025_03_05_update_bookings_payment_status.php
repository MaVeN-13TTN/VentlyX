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
        // For PostgreSQL, we need to modify the enum type
        DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_payment_status_check");
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_payment_status_check CHECK (payment_status::text = ANY (ARRAY['pending'::character varying, 'paid'::character varying, 'failed'::character varying, 'refunded'::character varying, 'cancelled'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original constraint
        DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_payment_status_check");
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_payment_status_check CHECK (payment_status::text = ANY (ARRAY['pending'::character varying, 'paid'::character varying, 'failed'::character varying, 'refunded'::character varying]::text[]))");
    }
};
