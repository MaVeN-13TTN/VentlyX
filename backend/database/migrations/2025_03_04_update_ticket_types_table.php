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
        Schema::table('ticket_types', function (Blueprint $table) {
            // Add status column if it doesn't exist
            if (!Schema::hasColumn('ticket_types', 'status')) {
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_types', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
