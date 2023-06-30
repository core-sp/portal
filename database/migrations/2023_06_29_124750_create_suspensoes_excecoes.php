<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\SuspensaoExcecao;

class CreateSuspensoesExcecoes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suspensoes_excecoes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cpf_cnpj')->nullable();
            $table->bigInteger('idrepresentante')->unsigned()->nullable();
            $table->foreign('idrepresentante')->references('id')->on('representantes');
            $table->date('data_inicial');
            $table->date('data_final')->nullable();
            $table->string('situacao')->default(SuspensaoExcecao::SITUACAO_SUSPENSAO);
            $table->bigInteger('agendamento_sala_id')->unsigned()->nullable();
            $table->foreign('agendamento_sala_id')->references('id')->on('agendamentos_salas');
            $table->text('justificativa')->nullable();
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
        Schema::dropIfExists('suspensoes_excecoes');
    }
}
