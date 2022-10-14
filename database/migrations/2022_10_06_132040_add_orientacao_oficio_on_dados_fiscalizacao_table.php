<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrientacaoOficioOnDadosFiscalizacaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dados_fiscalizacao', function (Blueprint $table) {
            $table->Integer('orientacaocontabil')->after('multaadministrativa')->default('0');
            $table->Integer('oficioprefeitura')->after('orientacaocontabil')->default('0');
            $table->Integer('oficioincentivo')->after('oficioprefeitura')->default('0');
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
            $table->dropColumn('orientacaocontabil');
            $table->dropColumn('oficioprefeitura');
            $table->dropColumn('oficioincentivo');
        });
    }
}
