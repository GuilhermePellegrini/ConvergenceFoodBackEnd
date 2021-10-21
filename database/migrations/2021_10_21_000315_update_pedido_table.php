<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePedidoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pedidos', function(Blueprint $table)
        {
            $table->foreignId('carrinho_id')->after('endereco_id')->nullable()->default(null);

            $table->foreign('carrinho_id')->references('id')->on('carrinhos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pedidos', function(Blueprint $table)
        {
            $table->dropForeign(['carrinho_id']);
            $table->dropColumn('carrinho_id');
        });
    }
}
