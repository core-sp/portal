<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSolicitaCedulaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicita_cedula', function (Blueprint $table) {
            $table->bigIncrements('id');
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
        Schema::dropIfExists('solicita_cedula');
    }
}
