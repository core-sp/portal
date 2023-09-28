<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalasReunioesBloqueios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salas_reunioes_bloqueios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sala_reuniao_id')->unsigned();
            $table->foreign('sala_reuniao_id')->references('id')->on('salas_reunioes');
            $table->date('dataInicial');
            $table->date('dataFinal')->nullable();
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
        Schema::dropIfExists('salas_reunioes_bloqueios');
    }
}
