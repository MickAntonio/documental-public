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
        Schema::create('entidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 45);
            $table->string('codigo', 45)->nullable();
            $table->string('nif', 45)->nullable();
            $table->boolean('activo');
            $table->string('endereco', 250)->nullable();
            $table->string('email', 45)->nullable();
            $table->string('telefone', 45)->nullable();
            $table->unsignedBigInteger('entidadeTipoId');
            $table->foreign('entidadeTipoId')->references('id')->on('entidade_tipos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->string('utilizadorId')->nullable();
            $table->foreign('utilizadorId')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->softDeletes();
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
        Schema::dropIfExists('entidades');
    }
};
