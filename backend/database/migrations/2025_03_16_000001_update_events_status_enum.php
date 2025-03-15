<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First drop the existing constraint
        DB::statement('ALTER TABLE events DROP CONSTRAINT IF EXISTS events_status_check');

        // Add the new constraint with all possible statuses
        DB::statement("ALTER TABLE events ADD CONSTRAINT events_status_check CHECK (
            status IN ('draft', 'published', 'cancelled', 'postponed', 'ended', 'archived')
        )");
    }

    public function down(): void
    {
        // Revert to original constraint
        DB::statement('ALTER TABLE events DROP CONSTRAINT IF EXISTS events_status_check');
        DB::statement("ALTER TABLE events ADD CONSTRAINT events_status_check CHECK (
            status IN ('draft', 'published', 'cancelled', 'completed')
        )");
    }
};
