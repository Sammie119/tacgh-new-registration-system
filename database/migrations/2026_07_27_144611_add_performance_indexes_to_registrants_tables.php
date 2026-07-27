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
        Schema::table('registrants_stage', function (Blueprint $table) {
            $table->index(['event_id', 'confirmed']);
        });

        Schema::table('registrants', function (Blueprint $table) {
            $table->index('stage_id');
            $table->index('room_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrants_stage', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'confirmed']);
        });

        Schema::table('registrants', function (Blueprint $table) {
            $table->dropIndex(['stage_id']);
            $table->dropIndex(['room_no']);
        });
    }
};
