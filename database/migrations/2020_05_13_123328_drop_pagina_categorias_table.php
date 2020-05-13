<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPaginaCategoriasTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('pagina_categorias');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('pagina_categorias', function (Blueprint $table) {
            $table->bigIncrements('idpaginacategoria');
            $table->string('nome');
            $table->bigInteger('idusuario')->unsigned();
            $table->foreign('idusuario')->references('idusuario')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
