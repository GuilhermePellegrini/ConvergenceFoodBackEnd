<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLojasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lojas', function (Blueprint $table) {
            $table->id();
            $table->string('corporate_name', 255);
            $table->string('trading_name', 20);
            $table->string('cnpj', 14)->unique();
            $table->string('web_site', 255)->nullable();
            $table->string('phone', 11);
            $table->string('cel_phone', 11)->nullable();
            $table->string('email', 255)->unique();
            $table->string('representante_legal', 255);
            $table->string('representante_legal_email', 255)->unique();
            $table->foreignId('endereco_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('endereco_id')->references('id')->on('enderecos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lojas');
    }
}
