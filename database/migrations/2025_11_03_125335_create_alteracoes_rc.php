<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlteracoesRc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alteracoes_rc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('informacao', 100);
            $table->string('valor_antigo', 255);
            $table->string('valor_atual', 255);
            $table->boolean('aceito')->nullable();
            $table->bigInteger('bdo_representante_id')->unsigned()->nullable();
            $table->foreign('bdo_representante_id')->references('id')->on('bdo_representantes');
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
        Schema::dropIfExists('alteracoes_rc');
    }
}
