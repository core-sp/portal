<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToRepresentanteEnderecosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('representante_enderecos', function (Blueprint $table) {
            $table->string('status')->after('crimage')->default('Aguardando confirmação');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('representante_enderecos', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
