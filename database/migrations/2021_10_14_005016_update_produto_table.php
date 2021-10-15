<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProdutoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('produtos', function(Blueprint $table)
        {
            $table->foreignId('categoria_id')->after('loja_id')->nullable()->default(null);

            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produtos', function(Blueprint $table)
        {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
        });
    }
}
