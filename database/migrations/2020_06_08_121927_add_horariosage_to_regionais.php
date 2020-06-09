<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHorariosageToRegionais extends Migration
{
    public function up()
    {
        Schema::table('regionais', function (Blueprint $table) {
            $table->string('horariosage')->nullable()->after('ageporhorario');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('regionais', function (Blueprint $table) {
            $table->dropColumn('horariosage');
        });
    }
}
