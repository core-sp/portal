<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUltimoAcessoOnSessoesAndRepresentantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sessoes', function (Blueprint $table) {
            $table->dateTime('ultimo_acesso')->nullable()->after('ip_address');
        });

        Schema::table('representantes', function (Blueprint $table) {
            $table->dateTime('ultimo_acesso')->nullable()->after('ativo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sessoes', function (Blueprint $table) {
            $table->dropColumn('ultimo_acesso');
        });

        Schema::table('representantes', function (Blueprint $table) {
            $table->dropColumn('ultimo_acesso');
        });
    }
}
