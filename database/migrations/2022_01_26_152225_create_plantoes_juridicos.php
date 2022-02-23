<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlantoesJuridicos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plantoes_juridicos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('idregional')->unsigned();
            $table->foreign('idregional')->references('idregional')->on('regionais');
            $table->tinyInteger('qtd_advogados')->default(0);
            $table->string('horarios')->nullable();
            $table->date('dataInicial')->nullable();
            $table->date('dataFinal')->nullable();
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
        Schema::dropIfExists('plantoes_juridicos');
    }
}
