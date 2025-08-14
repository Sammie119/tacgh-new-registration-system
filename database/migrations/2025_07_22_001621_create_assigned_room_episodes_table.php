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
        Schema::create('assigned_room_episodes', function (Blueprint $table) {
            $table->id();
            $table->integer('room_id');
            $table->bigInteger('event_id');
            $table->bigInteger('registrant_id');
            $table->bigInteger('accommodation_episodes_id')->nullable();
            $table->date('checkin_date');
            $table->date('checkout_date')->nullable();
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
        Schema::dropIfExists('assigned_room_episodes');
    }
};
