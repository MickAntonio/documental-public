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
        Schema::create('encaminhamento_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('encaminhamentoId');
            $table->boolean('visualizado');
            $table->text('mensagem')->nullable();
            $table->enum('accao', ['APROVADO', 'VERIFICADO', 'ASSINADO', 'REJEITADO', 'NAO_VERIFICADO', 'NAO_ASSINADO'])->nullable();
            $table->enum('tipo', ['UTILIZADOR', 'GRUPO', 'ENTIDADE']);
            $table->string('utilizadorId')->nullable();
            $table->string('grupoId')->nullable();
            $table->unsignedBigInteger('entidadeId')->nullable();
            $table->boolean('pendente')->default(true);
            $table->foreign('encaminhamentoId')->references('id')->on('encaminhamentos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('utilizadorId')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('entidadeId')->references('id')->on('entidades')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('encaminhamento_destinatarios');
    }
};
