<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiaHoraDesativaOnAvisosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avisos', function (Blueprint $table) {
            $table->dateTime('dia_hora_ativar')->nullable()->after('cor_fundo_titulo');
            $table->dateTime('dia_hora_desativar')->nullable()->after('dia_hora_ativar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avisos', function (Blueprint $table) {
            $table->dropColumn('dia_hora_ativar');
            $table->dropColumn('dia_hora_desativar');
        });
    }
}
