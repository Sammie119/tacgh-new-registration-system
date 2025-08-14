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
        Schema::create('accommodation_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('residence_id');
            $table->integer('total_rooms');
            $table->integer('total_floors');
            $table->string('gender', 1);
            $table->enum('status', ['Active', 'Blocked'])->default('Active');
            $table->tinyInteger('active_flag')->default(1);
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
        Schema::dropIfExists('accommodation_blocks');
    }
};
