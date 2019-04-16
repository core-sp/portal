<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgendamentoBloqueiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agendamento_bloqueios', function (Blueprint $table) {
            $table->bigIncrements('idagendamentobloqueio');
            $table->date('diainicio')->nullable();
            $table->date('diatermino')->nullable();
            $table->string('horainicio');
            $table->string('horatermino');
            $table->bigInteger('idregional')->unsigned()->nullable();
            $table->foreign('idregional')->references('idregional')->on('regionais');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agendamento_bloqueios');
    }
}
