<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlantoesJuridicosBloqueios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plantoes_juridicos_bloqueios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('idplantaojuridico')->unsigned();
            $table->foreign('idplantaojuridico')->references('id')->on('plantoes_juridicos');
            $table->date('dataInicial');
            $table->date('dataFinal');
            $table->string('horarios');
            $table->bigInteger('idusuario')->unsigned();
            $table->foreign('idusuario')->references('idusuario')->on('users');
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
        Schema::dropIfExists('plantoes_juridicos_bloqueios');
    }
}
