<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContabeisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contabeis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cnpj', 14)->unique();
            $table->string('nome')->nullable();
            $table->string('email')->nullable();
            $table->string('nome_contato')->nullable();
            $table->string('telefone')->nullable();
            $table->string('verify_token')->nullable();
            $table->string('password')->nullable();
            $table->boolean('ativo')->nullable();
            $table->boolean('aceite')->nullable();
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
        Schema::dropIfExists('contabeis');
    }
}
