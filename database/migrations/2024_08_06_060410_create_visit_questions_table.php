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
        Schema::create('question_sets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->foreignId('visit_id')->constrained();
            $table->foreignId('question_id')->constrained();
            $table->string('question_type');
            $table->string('question_order');
            $table->string("selected_key");
            $table->string("selected_answer");
            $table->timestampTz("answered_date");
            $table->timestamps();
            $table->foreignId("profile_id")->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_questions');
    }
};
