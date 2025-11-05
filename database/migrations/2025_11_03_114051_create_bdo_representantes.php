<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBdoRepresentantes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bdo_representantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('idrepresentante')->unsigned()->nullable();
            $table->foreign('idrepresentante')->references('id')->on('representantes');
            $table->string('descricao', 700);
            $table->string('endereco', 150);
            $table->string('email', 150);
            $table->string('telefone', 30);
            $table->string('segmento', 100);
            $table->json('regioes');
            $table->json('justificativas')->nullable();
            $table->json('status');
            $table->bigInteger('idusuario')->unsigned()->nullable();
            $table->foreign('idusuario')->references('idusuario')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bdo_representantes');
    }
}
