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
        Schema::create('valor_atributo_dinamicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registoId');
            $table->unsignedBigInteger('atributoDinamicoId');
            $table->text('valor')->nullable();
            $table->unique(['registoId', 'atributoDinamicoId']);
            $table->foreign('registoId')->references('id')->on('registos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('atributoDinamicoId')->references('id')->on('atributo_dinamicos')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('valor_atributo_dinamicos');
    }
};
