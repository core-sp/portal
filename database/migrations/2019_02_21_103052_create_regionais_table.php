<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionaisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regionais', function (Blueprint $table) {
            $table->bigIncrements('idregional');
            $table->string('prefixo');
            $table->string('regional');
            $table->string('endereco');
            $table->string('bairro');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('cep');
            $table->string('telefone');
            $table->string('fax')->nullable();
            $table->string('email');
            $table->string('funcionamento')->nullable();
            $table->string('responsavel')->nullable();
            $table->text('descricao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regionais');
    }
}
