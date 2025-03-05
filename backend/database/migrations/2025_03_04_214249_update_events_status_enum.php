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
        // For PostgreSQL, we need to drop the constraint first
        DB::statement("ALTER TABLE events DROP CONSTRAINT IF EXISTS events_status_check");

        // Add the new enum value
        DB::statement("ALTER TABLE events ADD CONSTRAINT events_status_check CHECK (status::text = ANY (ARRAY['draft'::text, 'published'::text, 'cancelled'::text, 'completed'::text, 'archived'::text]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE events DROP CONSTRAINT IF EXISTS events_status_check");
        DB::statement("ALTER TABLE events ADD CONSTRAINT events_status_check CHECK (status::text = ANY (ARRAY['draft'::text, 'published'::text, 'cancelled'::text, 'completed'::text]))");
    }
};
