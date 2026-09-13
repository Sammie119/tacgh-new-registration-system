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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('event_id');
            $table->string('name');
            $table->enum('applies_to', ['accommodation', 'registration_fee', 'both'])->default('both');
            $table->decimal('discount_percentage', 5, 2);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
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
        Schema::dropIfExists('promotions');
    }
};
