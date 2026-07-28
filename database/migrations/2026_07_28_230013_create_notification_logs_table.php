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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel'); // 'sms' | 'whatsapp'
            $table->string('recipient');
            $table->text('message');
            $table->boolean('success')->default(false);
            $table->text('response')->nullable(); // raw provider response / error string
            $table->unsignedBigInteger('registrant_id')->nullable(); // registrants_stage.id
            $table->unsignedBigInteger('event_id')->nullable();
            $table->timestamps();

            $table->index(['channel', 'success']);
            $table->index('registrant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
