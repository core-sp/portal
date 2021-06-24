<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreCadastrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pre_cadastros', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Informação da requisição de pré-cadastro
            $table->string('tipo')->nullable();
            $table->string('status')->nullable();
            $table->string('motivo')->nullable();

            // Informações de pessoa física
            $table->string('nome')->nullable();
            $table->string('cpf')->nullable();
            $table->string('tipoDocumento')->nullable();
            $table->string('numeroDocumento')->nullable();
            $table->string('orgaoEmissorDocumento')->nullable();
            $table->string('dataEmissaoDocumento')->nullable();
            $table->date('dataNascimento')->nullable();
            $table->string('estadoCivil')->nullable();
            $table->string('sexo')->nullable();
            $table->string('naturalizado')->nullable();
            $table->string('nacionalidade')->nullable();
            $table->string('nomePai')->nullable();
            $table->string('nomeMae')->nullable();

            // Informações de pessoa jurídica
            $table->string('cnpj')->nullable();
            $table->string('razaoSocial')->nullable();
            $table->string('formaRegistro')->nullable();
            $table->string('numeroRegistro')->nullable();
            $table->date('dataRegistro')->nullable();
            $table->string('ramoAtividade')->nullable();

            // Informações do CORE
            $table->string('capitalSocial')->nullable();
            $table->string('segmento')->nullable();

            // Informações de contato
            $table->string('email')->nullable();
            $table->string('celular')->nullable();
            $table->string('telefoneFixo')->nullable();

            // Informação de endereço
            $table->string('cep')->nullable();
            $table->string('bairro')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('estado')->nullable();
            $table->string('municipio')->nullable();
            
            
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
        Schema::dropIfExists('pre_cadastros');
    }
}
