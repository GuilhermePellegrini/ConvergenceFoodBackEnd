<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loja_id');
            $table->foreignId('user_id');
            $table->foreignId('endereco_id');
            $table->foreignId('cupom_id')->nullable()->default(null);
            $table->float('price', 10, 2);
            $table->float('discount', 10, 2)->nullable()->default(null);
            $table->string('note', 255)->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('loja_id')->references('id')->on('lojas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('endereco_id')->references('id')->on('enderecos')->onDelete('cascade');
            $table->foreign('cupom_id')->references('id')->on('cupons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
}
