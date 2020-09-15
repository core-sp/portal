<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertidoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certidoes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tipo');
            $table->string('codigo')->nullable();
            $table->string('tipo_pessoa');
            $table->string('nome');
            $table->string('cpf_cnpj');
            $table->string('registro_core');
            $table->date('data_inscricao');
            $table->string('tipo_empresa')->nullable();
            $table->string('resp_tecnico')->nullable();
            $table->string('resp_tecnico_registro_core')->nullable();
            $table->time('hora_emissao');
            $table->date('data_emissao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certidoes');
    }
}
