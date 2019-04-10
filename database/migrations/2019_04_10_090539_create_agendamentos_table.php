<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgendamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->bigIncrements('idagendamento');
            $table->string('nome');
            $table->string('cpf');
            $table->string('email');
            $table->string('celular');
            $table->date('dia');
            $table->string('hora');
            $table->string('protocolo');
            $table->string('tiposervico');
            $table->bigInteger('idregional')->unsigned()->nullable();
            $table->foreign('idregional')->references('idregional')->on('regionais');
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
        Schema::dropIfExists('agendamentos');
    }
}
