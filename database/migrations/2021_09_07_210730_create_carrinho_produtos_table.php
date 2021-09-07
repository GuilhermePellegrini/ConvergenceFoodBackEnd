<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarrinhoProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carrinho_produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrinho_id');
            $table->foreignId('produto_id');
            $table->string('note', 255);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('carrinho_id')->references('id')->on('carrinhos')->onDelete('cascade');
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
        Schema::dropIfExists('carrinho_produtos');
    }
}
