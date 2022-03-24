<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQtdAtendentesOnAgendamentoBloqueiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agendamento_bloqueios', function (Blueprint $table) {
            $table->tinyInteger('qtd_atendentes')->nullable()->after('horarios');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agendamento_bloqueios', function (Blueprint $table) {
            $table->dropColumn('qtd_atendentes');
        });
    }
}
