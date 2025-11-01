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

        Schema::create('documento_associados', function (Blueprint $table) {
            $table->id();
            $table->string('utilizador', 200);
            $table->unsignedBigInteger('documentoId');
            $table->unsignedBigInteger('registoId');
            $table->foreign('registoId')->references('id')->on('registos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('documentoId')->references('id')->on('documentos')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('documento_associados');
    }
};
