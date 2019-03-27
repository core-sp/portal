<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDboEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdo_empresas', function (Blueprint $table) {
            $table->bigIncrements('idempresa');
            $table->string('segmento');
            $table->string('cnpj');
            $table->string('razaosocial');
            $table->string('descricao');
            $table->string('endereco');
            $table->string('email');
            $table->string('telefone');
            $table->string('site')->nullable();
            $table->bigInteger('idusuario')->unsigned();
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
        Schema::dropIfExists('bdo_empresas');
    }
}
