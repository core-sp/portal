<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTermosConsentimentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('termos_consentimentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ip');
            $table->string('email')->nullable();
            $table->bigInteger('idrepresentante')->unsigned()->nullable();
            $table->foreign('idrepresentante')->references('id')->on('representantes');
            $table->bigInteger('idnewsletter')->unsigned()->nullable();
            $table->foreign('idnewsletter')->references('idnewsletter')->on('newsletters');
            $table->bigInteger('idagendamento')->unsigned()->nullable();
            $table->foreign('idagendamento')->references('idagendamento')->on('agendamentos');
            $table->bigInteger('idbdo')->unsigned()->nullable();
            $table->foreign('idbdo')->references('idoportunidade')->on('bdo_oportunidades');
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
        Schema::dropIfExists('termos_consentimentos');
    }
}
