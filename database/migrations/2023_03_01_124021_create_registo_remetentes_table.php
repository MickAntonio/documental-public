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
        Schema::create('registo_remetentes', function (Blueprint $table) {
            $table->id();
            $table->string("tipo",200);
            $table->unsignedBigInteger('entidadeId')->nullable();
            $table->unsignedBigInteger('registoId');
            $table->string('utilizadorId')->nullable();
            $table->string('grupoId')->nullable();
            $table->foreign('utilizadorId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('entidadeId')->references('id')->on('entidades')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('registoId')->references('id')->on('registos')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('registo_remetentes');
    }
};
