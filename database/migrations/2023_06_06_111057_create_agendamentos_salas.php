<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgendamentosSalas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agendamentos_salas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('protocolo');
            $table->bigInteger('idrepresentante')->unsigned()->nullable();
            $table->foreign('idrepresentante')->references('id')->on('representantes');
            $table->text('participantes')->nullable();
            $table->date('dia');
            $table->string('periodo');
            $table->boolean('periodo_todo')->default(false);
            $table->string('tipo_sala');
            $table->string('status')->nullable();
            $table->text('justificativa')->nullable();
            $table->string('anexo')->nullable();
            $table->bigInteger('sala_reuniao_id')->unsigned()->nullable();
            $table->foreign('sala_reuniao_id')->references('id')->on('salas_reunioes');
            $table->text('justificativa_admin')->nullable();
            $table->bigInteger('idusuario')->unsigned()->nullable();
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
        Schema::dropIfExists('agendamentos_salas');
    }
}
