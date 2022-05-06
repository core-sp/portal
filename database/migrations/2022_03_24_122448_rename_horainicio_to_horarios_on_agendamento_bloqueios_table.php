<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameHorainicioToHorariosOnAgendamentoBloqueiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agendamento_bloqueios', function (Blueprint $table) {
            $table->renameColumn('horainicio', 'horarios');
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
            $table->renameColumn('horarios', 'horainicio');
        });
    }
}
