<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSolicitacoesCedulasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitacoes_cedulas', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->bigInteger('idregional')->unsigned();
			$table->foreign('idregional')->references('idregional')->on('regionais');
            $table->string('cep');
            $table->string('bairro');
            $table->string('logradouro');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('estado');
            $table->string('municipio');
            $table->string('status')->default('Em andamento');
            $table->text('justificativa')->nullable();
            $table->bigInteger('idrepresentante')->unsigned();
            $table->foreign('idrepresentante')->references('id')->on('representantes');
            $table->bigInteger('idusuario')->unsigned()->nullable();
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
        Schema::dropIfExists('solicitacoes_cedulas');
    }
}
