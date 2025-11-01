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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('nif', 100)->nullable();
            $table->string('dataAbertura', 45)->nullable();
            $table->string('endereco', 150)->nullable();
            $table->string('email', 100);
            $table->string('telefone', 100)->nullable();
            $table->string('actividadesEconomica', 150)->nullable();
            $table->string('naturezaJuridica', 150)->nullable();
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
        Schema::dropIfExists('empresas');
    }
};
