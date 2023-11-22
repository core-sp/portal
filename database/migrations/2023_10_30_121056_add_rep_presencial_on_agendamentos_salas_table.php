<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepPresencialOnAgendamentosSalasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agendamentos_salas', function (Blueprint $table) {
            $table->text('rep_presencial')->nullable()->after('idrepresentante');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agendamentos_salas', function (Blueprint $table) {
            $table->dropColumn('rep_presencial');
        });
    }
}
