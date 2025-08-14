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
        Schema::create('online_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('reg_id');
            $table->string('payment_mode')->nullable();
            $table->string('transaction_no')->nullable();
            $table->decimal('amount_to_pay', 10, 2)->nullable();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->date('date_paid')->nullable();
            $table->string('comment')->nullable();
            $table->string('approved')->nullable();
            $table->date('approved_at')->nullable();
            $table->bigInteger('batch_no')->nullable();
            $table->decimal('event_total_fee', 10 , 2)->nullable();
            $table->string('payment_token')->nullable();
            $table->tinyInteger('payment_status')->default(0);
            $table->string('event_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_payments');
    }
};
