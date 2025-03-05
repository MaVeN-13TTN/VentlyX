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
        Schema::table('events', function (Blueprint $table) {
            // Add venue column
            $table->string('venue')->nullable()->after('location');

            // Add category column
            $table->string('category')->nullable()->after('organizer_id');

            // Rename capacity to max_capacity
            $table->renameColumn('capacity', 'max_capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('venue');
            $table->dropColumn('category');
            $table->renameColumn('max_capacity', 'capacity');
        });
    }
};
