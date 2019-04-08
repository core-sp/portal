<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaginaCategoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pagina_categorias');
    }
}
