<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registos', function (Blueprint $table) {
            $table->id();
            $table->string('referencia', 150)->nullable();
            $table->string('assunto', 300);
            $table->string('criadoPor', 150)->nullable();
            $table->string('localizacaoFisica', 200)->nullable();
            $table->date('data')->nullable();
            $table->integer('confidencial')->nullable();
            $table->enum('registoTipo', ['ENTRADA', 'SAIDA', 'INTERNO'])->nullable();
            $table->string('utilizadorId');
            $table->string('protocolo')->nullable();
            $table->unsignedBigInteger('tipoId');
            $table->unsignedBigInteger('estadoId');
            $table->foreign('utilizadorId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('estadoId')->references('id')->on('estados')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('tipoId')->references('id')->on('tipos')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registos');
    }
};
