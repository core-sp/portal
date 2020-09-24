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
            $table->smallInteger('ano')->unsigned();
            $table->foreign('ano')->references('ano')->on('anos_fiscalizacao');
            $table->Integer('notificacaopf')->default('0');
            $table->Integer('notificacaopj')->default('0');
            $table->Integer('constatacaopf')->default('0');
            $table->Integer('constatacaopj')->default('0');
            $table->Integer('infracaopf')->default('0');
            $table->Integer('infracaopj')->default('0');
            $table->Integer('convertidopf')->default('0');
            $table->Integer('convertidopj')->default('0');
            $table->Integer('orientacao')->default('0');
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
