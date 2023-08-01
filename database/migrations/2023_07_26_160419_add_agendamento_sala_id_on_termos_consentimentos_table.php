<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgendamentoSalaIdOnTermosConsentimentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('termos_consentimentos', function (Blueprint $table) {
            $table->bigInteger('agendamento_sala_id')->unsigned()->nullable()->after('idcursoinscrito');
            $table->foreign('agendamento_sala_id')->references('id')->on('agendamentos_salas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('termos_consentimentos', function (Blueprint $table) {
            $table->dropColumn('agendamento_sala_id');
        });
    }
}
