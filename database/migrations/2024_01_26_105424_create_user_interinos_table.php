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
        Schema::create('user_interinos', function (Blueprint $table) {
            $table->id();
            $table->string('utilizadorId');
            $table->string('utilizadorInterinoId');
            $table->date('periodoInicio')->nullable();
            $table->date('periodoFim')->nullable();
            $table->foreign('utilizadorId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('utilizadorInterinoId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_interinos');
    }
};
