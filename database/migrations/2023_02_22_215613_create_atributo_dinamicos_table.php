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
        Schema::create('atributo_dinamicos', function (Blueprint $table) {
            $table->id();
            $table->string('key', 150);
            $table->string('value', 150)->nullable();
            $table->string('label');
            $table->string('required', 150)->nullable();
            $table->string('order', 150)->nullable();;
            $table->enum('controlType', ['textbox', 'select', 'checkbox', 'date']);
            $table->enum('type', ['number', 'text'])->nullable();
            $table->string('options', 150)->nullable();
            $table->unsignedBigInteger('tipoId');
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
        Schema::dropIfExists('atributo_dinamicos');
    }
};
