<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBdoOportunidadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdo_oportunidades', function (Blueprint $table) {
            $table->bigIncrements('idoportunidade');
            $table->bigInteger('idempresa')->unsigned();
            $table->foreign('idempresa')->references('idempresa')->on('bdo_empresas');
            $table->string('segmento');
            $table->text('descricao');
            $table->integer('vagasdisponiveis');
            $table->integer('vagaspreenchidas')->nullable();
            $table->string('status')->nullable();
            $table->date('datainicio')->nullable();
            $table->bigInteger('idusuario')->unsigned();
            $table->foreign('idusuario')->references('idusuario')->on('users');
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
        Schema::dropIfExists('bdo_oportunidades');
    }
}
