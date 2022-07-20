<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposOnDadosFiscalizacaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dados_fiscalizacao', function (Blueprint $table) {
            $table->Integer('autoconstatacao')->after('cooperacaoinstitucional')->default('0');
            $table->Integer('autosdeinfracao')->after('autoconstatacao')->default('0');
            $table->Integer('multaadministrativa')->after('autosdeinfracao')->default('0');
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
            $table->dropColumn('autoconstatacao');
            $table->dropColumn('autosdeinfracao');
            $table->dropColumn('multaadministrativa');
        });
    }
}
