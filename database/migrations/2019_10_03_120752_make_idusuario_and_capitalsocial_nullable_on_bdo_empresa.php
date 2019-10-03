<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeIdusuarioAndCapitalsocialNullableOnBdoEmpresa extends Migration
{
    public function up()
    {
        Schema::table('bdo_empresas', function($table) {
            $table->bigInteger('idusuario')->unsigned()->nullable()->change();
            $table->string('capitalsocial')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('bdo_empresas', function($table) {
            $table->bigInteger('idusuario')->unsigned()->nullable(false)->change();
            $table->string('capitalsocial')->nullable(false)->change();
        });
    }
}
