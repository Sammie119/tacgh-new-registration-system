<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * venue_id already exists on the live events table (added manually,
     * outside of any migration, same as the vw_registration view). Guarded
     * with hasColumn so re-running this against the existing database is a
     * no-op rather than a "duplicate column" error.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'venue_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->integer('venue_id')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('events', 'venue_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('venue_id');
            });
        }
    }
};
