<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreRepresentantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pre_representantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cpf_cnpj')->unique();
            $table->string('nome');
            $table->string('email');
            $table->string('verify_token')->nullable();
            $table->string('password');
            $table->boolean('ativo')->default(0);
            $table->rememberToken();
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
        Schema::dropIfExists('pre_representantes');
    }
}
