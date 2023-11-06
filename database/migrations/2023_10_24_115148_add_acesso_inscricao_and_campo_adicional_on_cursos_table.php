<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Curso;

class AddAcessoInscricaoAndCampoAdicionalOnCursosTable extends Migration
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
            $table->string('acesso')->default(Curso::ACESSO_PRI)->after('resumo');
            $table->boolean('add_campo')->default(false)->after('publicado');
            $table->string('campo_rotulo')->nullable()->after('add_campo');
            $table->boolean('campo_required')->default(false)->after('campo_rotulo');
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
            $table->dropColumn(['inicio_inscricao', 'termino_inscricao', 'acesso', 'add_campo', 'campo_rotulo', 'campo_required']);
        });
    }
}
