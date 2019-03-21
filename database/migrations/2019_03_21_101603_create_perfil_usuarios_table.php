<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePerfilUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perfil_usuarios', function (Blueprint $table) {
            $table->bigInteger('idperfil')->unsigned();
            $table->foreign('idperfil')->references('idperfil')->on('perfis');
            $table->bigInteger('idusuario')->unsigned();
            $table->foreign('idusuario')->references('idusuario')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('perfil_usuarios');
    }
}
