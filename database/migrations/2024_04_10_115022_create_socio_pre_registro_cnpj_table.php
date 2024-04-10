<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSocioPreRegistroCnpjTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('socio_pre_registro_cnpj', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('pre_registro_cnpj_id')->unsigned();
            $table->foreign('pre_registro_cnpj_id')->references('id')->on('pre_registros_cnpj');
            $table->bigInteger('socio_id')->unsigned();
            $table->foreign('socio_id')->references('id')->on('socios');
            $table->string('historico_socio')->nullable();
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
        Schema::dropIfExists('socio_pre_registro_cnpj');
    }
}
