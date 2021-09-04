<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimentoEstoquesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimento_estoques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->nullable()->default(null);
            $table->foreignId('entrada_id')->nullable()->default(null);
            $table->foreignId('estoque_id');
            $table->foreignId('produto_id');
            $table->integer('entrada');
            $table->integer('saida');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('estoque_id')->references('id')->on('estoques')->onDelete('cascade');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movimento_estoques');
    }
}
