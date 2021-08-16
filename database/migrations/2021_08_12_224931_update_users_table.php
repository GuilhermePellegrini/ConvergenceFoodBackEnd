<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->string('cpf', 14)->after('name')->nullable()->default(null);
            $table->foreignId('endereco_id')->after('password')->nullable()->default(null);
            $table->foreignId('loja_id')->after('endereco_id')->nullable()->default(null);
            $table->boolean('admin')->default(false)->before('loja_id')->after('loja_id');
            $table->softDeletes();
            $table->foreign('endereco_id')->references('id')->on('enderecos');
            $table->foreign('loja_id')->references('id')->on('lojas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropForeign('users_endereco_id_foreign');
            $table->dropForeign('users_loja_id_foreign');
            $table->dropColumn('endereco_id');
            $table->dropColumn('loja_id');
            $table->dropColumn('deleted_at');
            $table->dropColumn('admin');
            $table->dropColumn('cpf');
        });
    }
}
