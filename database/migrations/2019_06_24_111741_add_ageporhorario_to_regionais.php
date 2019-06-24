<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAgeporhorarioToRegionais extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('regionais', function (Blueprint $table) {
            $table->tinyInteger('ageporhorario')->default('1')->after('funcionamento');
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
            $table->dropColumn('ageporhorario');
        });
    }
}
