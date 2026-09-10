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
            $table->boolean('is_student')->default(false)->after('disability');
            $table->string('institution_name')->nullable()->after('is_student');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrants_stage', function (Blueprint $table) {
            $table->dropColumn(['is_student', 'institution_name']);
        });
    }
};
