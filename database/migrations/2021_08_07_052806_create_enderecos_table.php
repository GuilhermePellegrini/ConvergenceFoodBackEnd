<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEnderecosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('cep', 8);
            $table->string('address', 255);
            $table->string('district', 255);
            $table->string('number', 100);
            $table->string('complement', 255)->nullable();
            $table->foreignId('cidade_id');
            $table->foreignId('estado_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('cidade_id')->references('id')->on('cidades');
            $table->foreign('estado_id')->references('id')->on('estados');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('enderecos');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
