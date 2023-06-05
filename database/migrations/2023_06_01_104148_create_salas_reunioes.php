<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalasReunioes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salas_reunioes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('idregional')->unsigned();
            $table->foreign('idregional')->references('idregional')->on('regionais');
            $table->string('horarios_reuniao')->default(json_encode(['manha' => array(), 'tarde' => array()], JSON_FORCE_OBJECT));
            $table->string('horarios_coworking')->default(json_encode(['manha' => array(), 'tarde' => array()], JSON_FORCE_OBJECT));
            $table->unsignedTinyInteger('participantes_reuniao')->default(0);
            $table->unsignedTinyInteger('participantes_coworking')->default(0);
            $table->string('itens_reuniao')->default(json_encode(array(), JSON_FORCE_OBJECT));
            $table->string('itens_coworking')->default(json_encode(array(), JSON_FORCE_OBJECT));
            $table->bigInteger('idusuario')->unsigned();
            $table->foreign('idusuario')->references('idusuario')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salas_reunioes');
    }
}
