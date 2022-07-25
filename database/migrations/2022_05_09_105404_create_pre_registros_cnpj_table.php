<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreRegistrosCnpjTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pre_registros_cnpj', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('razao_social')->nullable();
            $table->string('nire', 20)->nullable();
            $table->string('tipo_empresa')->nullable();
            $table->date('dt_inicio_atividade')->nullable();
            $table->string('inscricao_municipal')->nullable();
            $table->string('inscricao_estadual')->nullable();
            $table->string('capital_social')->nullable();
            $table->string('cep', 10)->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero', 10)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->char('uf', 2)->nullable();
            $table->string('historico_rt')->nullable();
            $table->bigInteger('responsavel_tecnico_id')->unsigned()->nullable();
            $table->foreign('responsavel_tecnico_id')->references('id')->on('responsaveis_tecnicos');
            $table->bigInteger('pre_registro_id')->unsigned();
            $table->foreign('pre_registro_id')->references('id')->on('pre_registros');
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
        Schema::dropIfExists('pre_registros_cnpj');
    }
}
