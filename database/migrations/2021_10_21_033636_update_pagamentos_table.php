<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePagamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pagamentos', function(Blueprint $table)
        {
            $table->foreignId('user_id')->nullable()->default(null)->change();
            $table->foreignId('assinatura_id')->nullable()->default(null)->after('user_id');

            $table->foreign('assinatura_id')->references('id')->on('assinaturas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pagamentos', function(Blueprint $table)
        {
            $table->foreignId('user_id')->nullable(false)->change();

            $table->dropForeign(['assinatura_id']);
            $table->dropColumn('assinatura_id');
        });
    }
}
