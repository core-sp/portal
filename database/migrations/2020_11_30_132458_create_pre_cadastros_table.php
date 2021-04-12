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
            $table->string('tipo');
            $table->string('status');
            $table->string('cpf')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('nome');
            $table->string('email');
            $table->string('anexo1');
            $table->string('anexo2');
            $table->string('motivo')->nullable();
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
