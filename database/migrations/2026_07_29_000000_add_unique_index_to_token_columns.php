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
            $table->unique('token');
        });

        Schema::table('batch_logs', function (Blueprint $table) {
            $table->unique('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrants_stage', function (Blueprint $table) {
            $table->dropUnique(['token']);
        });

        Schema::table('batch_logs', function (Blueprint $table) {
            $table->dropUnique(['token']);
        });
    }
};
