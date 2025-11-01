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
        Schema::create('registo_permissoes', function (Blueprint $table) {
            $table->id();
            $table->string("tipo",200);
            $table->string('grupoId')->nullable();
            $table->unsignedBigInteger('entidadeId')->nullable();
            $table->unsignedBigInteger('registoId');
            $table->string('permissoes');
            $table->string('utilizadorId')->nullable();
            $table->foreign('utilizadorId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('entidadeId')->references('id')->on('entidades')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('registoId')->references('id')->on('registos')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registo_permissoes');
    }
};
