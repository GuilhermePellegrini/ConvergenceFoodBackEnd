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
            $table->id();
            $table->bigInteger('mercado_pago_id');
            $table->foreignId('pedido_id');
            $table->foreignId('user_id');
            $table->string('status');
            $table->string('status_detail');
            $table->datetime('date_created');
            $table->datetime('date_approved')->nullable()->default(null);
            $table->datetime('money_release_date')->nullable()->default(null);
            $table->float('transaction_amount', 10, 2);
            $table->float('transaction_amount_refunded', 10, 2);
            $table->string('payment_method_id');
            $table->string('payment_type_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
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
