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
        Schema::create('accommodation_episodes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('even_id');
            $table->bigInteger('accommodation_id');
            $table->enum('status', ['Pending', 'In-Progress', 'Completed'])->default('Pending');
            $table->date('start_date');
            $table->date('end_date')->nullable();
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
        Schema::dropIfExists('accommodation_episodes');
    }
};
