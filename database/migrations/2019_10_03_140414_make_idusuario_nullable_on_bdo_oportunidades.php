<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeIdusuarioNullableOnBdoOportunidades extends Migration
{
    public function up()
    {
        Schema::table('bdo_oportunidades', function($table) {
            $table->bigInteger('idusuario')->unsigned()->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('bdo_oportunidades', function($table) {
            $table->bigInteger('idusuario')->unsigned()->nullable(false)->change();
        });
    }
}
