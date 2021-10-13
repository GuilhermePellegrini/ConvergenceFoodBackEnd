<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnderecoUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('endereco_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('endereco_id');
            $table->foreignId('user_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('endereco_id')->references('id')->on('enderecos')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('users', function(Blueprint $table)
        {
            $table->dropForeign(['endereco_id']);
            $table->dropColumn('endereco_id');
        });

        Schema::table('enderecos', function(Blueprint $table)
        {
            $table->boolean('default')->default(false)->after('complement');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('endereco_users');
            
        Schema::table('users', function(Blueprint $table)
        {
            $table->foreignId('endereco_id')->after('password')->nullable()->default(null);
            $table->foreign('endereco_id')->references('id')->on('enderecos');
        });

        Schema::table('enderecos', function(Blueprint $table)
        {
            $table->dropColumn('default');
        });
        
    }
}
