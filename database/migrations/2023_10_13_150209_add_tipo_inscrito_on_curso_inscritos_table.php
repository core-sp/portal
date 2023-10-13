<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\CursoInscrito;

class AddTipoInscritoOnCursoInscritosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->string('tipo_inscrito')->default(CursoInscrito::INSCRITO_SITE)->after('registrocore');
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
            $table->dropColumn('tipo_inscrito');
        });
    }
}
