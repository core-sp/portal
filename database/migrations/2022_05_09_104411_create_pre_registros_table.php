<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\PreRegistro;

class CreatePreRegistrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pre_registros', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('segmento')->nullable();
            $table->string('registro_secundario')->nullable();
            $table->string('cep', 10)->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero', 10)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->char('uf', 2)->nullable();
            $table->string('telefone')->nullable();
            $table->string('tipo_telefone')->nullable();
            $table->string('opcional_celular')->nullable();
            $table->bigInteger('user_externo_id')->unsigned();
            $table->foreign('user_externo_id')->references('id')->on('users_externo');
            $table->bigInteger('contabil_id')->unsigned()->nullable();
            $table->foreign('contabil_id')->references('id')->on('contabeis');
            $table->bigInteger('idregional')->unsigned()->nullable();
            $table->foreign('idregional')->references('idregional')->on('regionais');
            $table->bigInteger('idusuario')->unsigned()->nullable();
            $table->foreign('idusuario')->references('idusuario')->on('users');
            $table->string('status')->default(PreRegistro::STATUS_CRIADO)->nullable();
            $table->text('justificativa')->nullable();
            $table->text('confere_anexos')->nullable();
            $table->string('historico_contabil')->nullable();
            $table->text('historico_status')->nullable();
            $table->text('campos_espelho')->nullable();
            $table->text('campos_editados')->nullable();
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
        Schema::dropIfExists('pre_registros');
    }
}
