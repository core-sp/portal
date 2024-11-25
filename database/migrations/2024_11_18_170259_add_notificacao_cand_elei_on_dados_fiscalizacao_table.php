<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotificacaoCandEleiOnDadosFiscalizacaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dados_fiscalizacao', function (Blueprint $table) {
            $table->Integer('notificacandidatoeleicao')->after('oficioincentivo')->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dados_fiscalizacao', function (Blueprint $table) {
            $table->dropColumn('notificacandidatoeleicao');
        });
    }
}
