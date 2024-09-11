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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->foreignId('agency_id')->constrained();
            $table->foreignId('patient_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestampTz('visit_start')->nullable();
            $table->timestampTz('visit_end')->nullable();
            $table->timestampTz('clock_in')->nullable();
            $table->timestampTz('clock_out')->nullable();
            $table->string('visit_type')->nullable();
            $table->string('schedule_type')->nullable();
            $table->boolean('status')->nullable();
            $table->boolean('is_complete')->default(false);
            $table->timestamps();
            $table->foreignId("profile_id")->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
