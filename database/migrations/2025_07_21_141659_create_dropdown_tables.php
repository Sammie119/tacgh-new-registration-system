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
        Schema::create('lookup_codes', function (Blueprint $table) {
            $table->id();
            $table->string('lookup_short_code', 10);
            $table->string('look_up_name', 50);
            $table->tinyInteger('active_flag');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lookups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('lookup_code_id');
            $table->string('full_name', 100)->nullable();
            $table->string('short_name', 10)->nullable();
            $table->binary('use_short_name')->nullable();
            $table->tinyInteger('active_flag')->default(0);
            $table->tinyInteger('toggled')->default(0);
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lookup_codes');
        Schema::dropIfExists('lookups');
    }
};
