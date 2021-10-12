<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMovimentoEstoque extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimento_estoques', function(Blueprint $table)
        {
            $table->enum('motivo',['vencimento', 'ajuste', 'entrada'])->after('saida')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movimento_estoques', function (Blueprint $table){
            $table->dropColumn('motivo');
        });
    }
}
