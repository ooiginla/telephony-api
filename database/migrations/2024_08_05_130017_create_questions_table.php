<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('name', 500)->nullable();
            $table->string('question', 500);
            $table->string('type', 20);
            $table->string('hash', 60);
            $table->json("choices")->nullable();
            $table->foreignId('agency_id')->constrained();
            $table->timestamps();
            $table->foreignId("profile_id")->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
