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
        Schema::create('tipo_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('templateId');
            $table->unsignedBigInteger('tipoId');
            $table->foreign('tipoId')->references('id')->on('tipos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('templateId')->references('id')->on('templates')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('tipo_templates');
    }
};
