<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepresentanteEnderecosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('representante_enderecos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('ass_id');
            $table->string('cep');
            $table->string('bairro');
            $table->string('logradouro');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('estado');
            $table->string('municipio');
            $table->string('crimage');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ass_id')->references('ass_id')->on('representantes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('representante_enderecos');
    }
}
