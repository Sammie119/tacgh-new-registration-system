<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AccommodationEpisodeController/Model were dead code: index() called a
     * nonexistent service method, store/update/etc. were empty stubs, no
     * view or other code referenced the model, and the table was empty.
     * Removing it along with the application code.
     */
    public function up(): void
    {
        Schema::dropIfExists('accommodation_episodes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
};
