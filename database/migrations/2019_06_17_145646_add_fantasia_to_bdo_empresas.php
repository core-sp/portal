<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFantasiaToBdoEmpresas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bdo_empresas', function (Blueprint $table) {
            $table->string('fantasia')->after('razaosocial')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bdo_empresas', function (Blueprint $table) {
            $table->dropColumn('fantasia');
        });
    }
}
