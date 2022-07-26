<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreRegistrosCpfTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pre_registros_cpf', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome_social')->nullable();
            $table->date('dt_nascimento')->nullable();
            $table->char('sexo', 1)->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('nacionalidade')->default('BRASILEIRA')->nullable();
            $table->string('naturalidade_cidade')->nullable();
            $table->string('naturalidade_estado')->nullable();
            $table->string('nome_mae')->nullable();
            $table->string('nome_pai')->nullable();
            $table->string('tipo_identidade')->nullable();
            $table->string('identidade')->nullable();
            $table->string('orgao_emissor')->nullable();
            $table->date('dt_expedicao')->nullable();
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
        Schema::dropIfExists('pre_registros_cpf');
    }
}
