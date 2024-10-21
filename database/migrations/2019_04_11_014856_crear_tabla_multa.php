<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaMulta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multa', function (Blueprint $table) {
            $table->increments('id_multa');
            $table->integer('reserva_id')->unsigned();
            $table->integer('total_multa');
            $table->boolean('estado_multa');
            $table->integer('usuario_multa')->unsigned();
            $table->timestamps();
            $table->foreign('reserva_id')->references('id_reserva')->on('reserva');
            $table->foreign('usuario_multa')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('multa', function($table) {
            $table->dropForeign(['reserva_id']);
            $table->dropColumn('reserva_id');
        });
        Schema::drop("multa");
    }
}
