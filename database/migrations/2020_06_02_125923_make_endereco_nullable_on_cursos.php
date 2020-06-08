<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeEnderecoNullableOnCursos extends Migration
{
    public function up()
    {
        Schema::table('cursos', function($table) {
            $table->string('endereco')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('cursos', function($table) {
            $table->string('endereco')->nullable(false)->change();
        });
    }
}
