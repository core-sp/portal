<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInicioInscricaoAndTerminoInscricaoOnCursosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dateTime('inicio_inscricao')->nullable()->after('datatermino');
            $table->dateTime('termino_inscricao')->nullable()->after('inicio_inscricao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn('inicio_inscricao');
            $table->dropColumn('termino_inscricao');
        });
    }
}
