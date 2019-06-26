<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFuncaoToHomeImagens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('home_imagens', function (Blueprint $table) {
            $table->string('funcao')->after('idimagem')->default('bannerprincipal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('home_imagens', function (Blueprint $table) {
            $table->dropColumn('funcao');
        });
    }
}
