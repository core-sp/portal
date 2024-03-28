<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChecksumOnCursoInscritosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->string('checksum')->nullable()->after('presenca');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->dropColumn('checksum');
        });
    }
}
