<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerfilPermissao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perfil_permissao', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('perfil_id')->unsigned();
            $table->foreign('perfil_id')->references('idperfil')->on('perfis');
            $table->bigInteger('permissao_id')->unsigned();
            $table->foreign('permissao_id')->references('idpermissao')->on('permissoes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('perfil_permissao');
    }
}
