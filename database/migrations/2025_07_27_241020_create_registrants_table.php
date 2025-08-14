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
        Schema::create('registrants_stage', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('first_name', 150);
            $table->string('surname', 150);
            $table->string('other_names', 150)->nullable();
            $table->integer('gender');
            $table->date('date_of_birth');
            $table->integer('marital_status');
            $table->integer('nationality_id');
            $table->string('phone_number', 50);
            $table->string('whatsapp_number', 50)->nullable();
            $table->string('email');
            $table->text('address');
            $table->integer('position_held');
            $table->integer('profession');
            $table->integer('residence_country_id');
            $table->string('languages_spoken');
            $table->boolean('need_accommodation')->default(true);
            $table->string('emergency_contacts_name');
            $table->string('emergency_contacts_relationship', 50)->nullable();
            $table->string('emergency_contacts_phone_number', 50)->nullable();
            $table->enum('attendance_type', ['In-Person', 'Online'])->default('In-Person');
            $table->string('event_id');
            $table->boolean('disability')->default(false);
            $table->text('special_needs')->nullable();
            $table->enum('confirmed', ['Yes', 'No'])->default('No');
            $table->string('token', 20);
            $table->bigInteger('batch_no')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('registrants', function (Blueprint $table) {
            $table->id();
            $table->string('registration_no', 20);
            $table->bigInteger('stage_id');
            $table->string('event_id');
            $table->integer('accommodation_type')->nullable();
            $table->decimal('accommodation_fee', 10, 2)->default(0.00);
            $table->integer('registration_type')->default(0);
            $table->decimal('registration_fee', 10,2)->default(0.00);
            $table->decimal('total_fee', 10,2)->default(0.00);
            $table->bigInteger('room_no')->nullable();
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->integer('check_in_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('batch_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('batch_no');
            $table->string('event_id');
            $table->string('email');
            $table->string('phone_number', 50)->nullable();
            $table->string('whatsapp_number', 50)->nullable();
            $table->enum('confirmed', ['Yes', 'No'])->default('No');
            $table->string('token', 20)->nullable();
            $table->decimal('total_registration_fees', 10,2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrants_stage');
        Schema::dropIfExists('registrants');
        Schema::dropIfExists('batch_logs');
    }
};
