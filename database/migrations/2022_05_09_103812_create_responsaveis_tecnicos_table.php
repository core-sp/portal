<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResponsaveisTecnicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('responsaveis_tecnicos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cpf', 11)->unique();
            $table->string('registro')->unique()->nullable();
            $table->string('nome')->nullable();
            $table->string('nome_social')->nullable();
            $table->char('sexo', 1)->nullable();
            $table->date('dt_nascimento')->nullable();
            $table->string('cep', 10)->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero', 10)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->char('uf', 2)->nullable();
            $table->string('nome_mae')->nullable();
            $table->string('nome_pai')->nullable();
            $table->string('tipo_identidade')->nullable();
            $table->string('identidade')->nullable();
            $table->string('orgao_emissor')->nullable();
            $table->date('dt_expedicao')->nullable();
            $table->string('titulo_eleitor')->nullable();
            $table->string('zona')->nullable();
            $table->string('secao')->nullable();
            $table->string('ra_reservista')->nullable();
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
        Schema::dropIfExists('responsaveis_tecnicos');
    }
}
