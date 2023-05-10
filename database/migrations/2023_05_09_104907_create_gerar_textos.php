<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGerarTextos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gerar_textos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tipo')->default('Título');
            $table->string('texto_tipo');
            $table->text('conteudo')->nullable();
            $table->boolean('com_numeracao')->default(true);
            $table->unsignedTinyInteger('ordem')->default(1);
            $table->string('nivel')->nullable();
            $table->string('tipo_doc')->default('Carta de serviços ao usuário');
            $table->string('indice')->nullable();
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
        Schema::dropIfExists('gerar_textos');
    }
}
