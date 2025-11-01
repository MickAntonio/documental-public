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
        Schema::create('encaminhamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registoId');
            $table->string('utilizadorId');
            $table->string('assunto');
            $table->text('mensagem')->nullable();
            $table->date('dataLimite')->nullable();
            $table->boolean('enviarEmail')->default(false);
            $table->boolean('receberNotificacao')->default(false);
            $table->enum('accaoSolicitada', ['APROVAR', 'VERIFICAR', 'ASSINAR', 'OUTRA']);
            $table->enum('accaoExecutada', ['APROVADO', 'VERIFICADO', 'ASSINADO', 'REJEITADO', 'NAO_VERIFICADO', 'NAO_ASSINADO'])->nullable();
            $table->boolean('obrigatorioTodos')->default(false);
            $table->boolean('pendente')->default(true);
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
        Schema::dropIfExists('encaminhamentos');
    }
};
