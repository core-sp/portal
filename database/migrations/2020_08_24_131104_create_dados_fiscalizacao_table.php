<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDadosFiscalizacaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dados_fiscalizacao', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('idregional')->unsigned();
            $table->foreign('idregional')->references('idregional')->on('regionais');
            $table->bigInteger('idperiodo')->unsigned();
            $table->foreign('idperiodo')->references('id')->on('periodos_fiscalizacao');
            $table->Integer('processofiscalizacaopf')->default('0');
            $table->Integer('processofiscalizacaopj')->default('0');
            $table->Integer('registroconvertidopf')->default('0');
            $table->Integer('registroconvertidopj')->default('0');
            $table->Integer('processoverificacao')->default('0');
            $table->Integer('dispensaregistro')->default('0');
            $table->Integer('notificacaort')->default('0');
            $table->Integer('orientacaorepresentada')->default('0');
            $table->Integer('orientacaorepresentante')->default('0');
            $table->Integer('cooperacaoinstitucional')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dados_fiscalizacao');
    }
}
