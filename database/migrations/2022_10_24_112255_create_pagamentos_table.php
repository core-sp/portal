<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payment_id');
            $table->string('cobranca_id');
            $table->string('total');
            $table->string('forma');
            $table->string('parcelas');
            $table->string('tipo_parcelas')->nullable();
            $table->string('bandeira')->nullable();
            $table->string('combined_id')->nullable();
            $table->string('payment_tag')->nullable();
            $table->boolean('is_3ds')->default(false);
            $table->string('status');
            $table->string('authorized_at');
            $table->string('canceled_at')->nullable();
            $table->boolean('gerenti_ok')->default(true);
            $table->text('transacao_temp')->nullable();
            $table->bigInteger('idrepresentante')->unsigned()->nullable();
            $table->foreign('idrepresentante')->references('id')->on('representantes');
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
        Schema::dropIfExists('pagamentos');
    }
}
