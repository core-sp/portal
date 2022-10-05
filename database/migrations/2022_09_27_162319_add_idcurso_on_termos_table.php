<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdcursoOnTermosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('termos_consentimentos', function (Blueprint $table) {
            $table->bigInteger('idcursoinscrito')->unsigned()->nullable()->after('idbdo');
            $table->foreign('idcursoinscrito')->references('idcursoinscrito')->on('curso_inscritos');
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
            $table->dropColumn('idcursoinscrito');
        });
    }
}
