<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersExterno extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_externo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cpf_cnpj', 14)->unique();
            $table->string('nome');
            $table->string('email');
            $table->string('verify_token')->nullable();
            $table->string('password')->nullable();
            $table->boolean('ativo')->default(0);
            $table->boolean('aceite')->default(0);
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
        Schema::dropIfExists('users_externo');
    }
}
