<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompromissosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compromissos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('titulo');
            $table->text('descricao');
            $table->text('local');
            $table->date('data');
            $table->time('horarioinicio');
            $table->time('horariotermino');
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
        Schema::dropIfExists('compromissos');
    }
}
