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
        Schema::create('accommodation_rooms', function (Blueprint $table) {
            $table->id();
            $table->integer('room_no');
            $table->integer('floor_no');
            $table->string('floor_name', 50);
            $table->integer('block_id');
            $table->integer('residence_id');
            $table->tinyInteger('total_occupants');
            $table->string('prefix', 50)->nullable();
            $table->string('suffix', 50)->nullable();
            $table->enum('type', ['Regular', 'Special', 'Reserved'])->default('Regular');
            $table->string('name', 50)->nullable();
            $table->tinyInteger('assign')->default(0);
            $table->char('gender')->default('A');
            $table->integer('special_acc')->default(0);
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
        Schema::dropIfExists('accommodation_rooms');
    }
};
