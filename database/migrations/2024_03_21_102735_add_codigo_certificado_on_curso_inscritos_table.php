<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodigoCertificadoOnCursoInscritosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->uuid('codigo_certificado')->nullable()->after('campo_adicional');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->dropColumn('codigo_certificado');
        });
    }
}
