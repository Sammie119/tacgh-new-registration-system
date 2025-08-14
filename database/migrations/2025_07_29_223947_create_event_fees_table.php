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
        Schema::create('event_fees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('event_id')->nullable();
            $table->enum('fee_type', ['accommodation', 'registration_fee'])->default('registration_fee');
            $table->string('description');
            $table->decimal('fee_amount', 10,2)->default(0.00);
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
        Schema::dropIfExists('event_fees');
    }
};
