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
        Schema::create('etiqueta_protocolos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 250);
            $table->integer('quantidade');
            $table->integer('duplicacao');
            $table->boolean('incluirData')->default(false);
            $table->string('utilizadorId')->nullable();
            $table->foreign('utilizadorId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('localizacao', 250);
            $table->text('codigos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etiqueta_protocolos');
    }
};
