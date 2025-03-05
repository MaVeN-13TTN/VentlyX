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
        // For PostgreSQL, drop the constraint first
        DB::statement("ALTER TABLE ticket_types DROP CONSTRAINT IF EXISTS ticket_types_status_check");

        // Add the new enum values
        DB::statement("ALTER TABLE ticket_types ADD CONSTRAINT ticket_types_status_check CHECK (status::text = ANY (ARRAY['draft'::text, 'active'::text, 'paused'::text, 'sold_out'::text, 'expired'::text, 'cancelled'::text]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE ticket_types DROP CONSTRAINT IF EXISTS ticket_types_status_check");
        DB::statement("ALTER TABLE ticket_types ADD CONSTRAINT ticket_types_status_check CHECK (status::text = ANY (ARRAY['draft'::text, 'paused'::text, 'sold_out'::text, 'expired'::text, 'cancelled'::text]))");
    }
};
