<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdusuarioToSolicitaCedulaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('solicita_cedula', function (Blueprint $table) {
            $table->bigInteger('idusuario')->unsigned()->nullable()->after('idrepresentante');
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
        Schema::table('solicita_cedula', function (Blueprint $table) {
            $table->dropColumn('idusuario');
        });
    }
}
