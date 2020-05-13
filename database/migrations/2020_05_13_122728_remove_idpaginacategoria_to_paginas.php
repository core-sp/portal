<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveIdpaginacategoriaToPaginas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paginas', function (Blueprint $table) {
            if (env('DB_CONNECTION') !== 'sqlite') {
                $table->dropForeign(['idpaginacategoria']);
            }
            $table->dropColumn('idpaginacategoria');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paginas', function (Blueprint $table) {
            $table->bigInteger('idpaginacategoria')->unsigned()->nullable();
            $table->foreign('idpaginacategoria')->references('idpaginacategoria')->on('pagina_categorias');
        });
    }
}
