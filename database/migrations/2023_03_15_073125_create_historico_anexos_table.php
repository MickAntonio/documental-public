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
        Schema::create('historico_anexos', function (Blueprint $table) {
            $table->id();
            $table->integer('anexoId')->nullable();
            $table->string('nome', 250);
            $table->integer('tamanho')->nullable();
            $table->decimal('versao')->nullable();
            $table->string('extensao')->nullable();
            $table->text('observacao')->nullable();
            $table->string('criadoPor', 45)->nullable();
            $table->string('editadoPor', 45)->nullable();
            $table->string('localizacao', 250)->nullable();
            $table->unsignedBigInteger('registoId')->nullable();
            $table->string('utilizadorId')->nullable();
            $table->foreign('registoId')->references('id')->on('registos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('utilizadorId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historico_anexos');
    }
};
