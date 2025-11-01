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
        Schema::create('anexo_pendentes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 250);
            $table->boolean('classificado')->default(false);
            $table->integer('tamanho')->nullable();
            $table->string('extensao')->nullable();
            $table->unsignedBigInteger('localizacaoScannerId')->nullable();
            $table->string('criadoPor', 45)->nullable();
            $table->string('localizacao', 250)->nullable();
            $table->string('utilizadorId')->nullable();
            $table->foreign('localizacaoScannerId')->references('id')->on('localizacao_scanners')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('utilizadorId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anexo_pendentes');
    }
};
