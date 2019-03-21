<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCursoInscritosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curso_inscritos', function (Blueprint $table) {
            $table->bigIncrements('idcursoinscrito');
            $table->string('cpf');
            $table->string('nome');
            $table->string('telefone');
            $table->string('email');
            $table->bigInteger('idcurso')->unsigned();
            $table->foreign('idcurso')->references('idcurso')->on('cursos');
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
        Schema::dropIfExists('curso_inscritos');
    }
}
