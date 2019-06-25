<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeImagensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_imagens', function (Blueprint $table) {
            $table->bigIncrements('idimagem');
            $table->tinyInteger('ordem')->nullable();
            $table->string('url')->nullable();
            $table->string('url_mobile')->nullable();
            $table->string('link')->default('#')->nullable();
            $table->string('target')->default('_self');
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
        Schema::dropIfExists('home_imagens');
    }
}
