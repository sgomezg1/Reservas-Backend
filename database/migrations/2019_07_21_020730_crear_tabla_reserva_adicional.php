<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaReservaAdicional extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserva_adicional', function (Blueprint $table) {
            $table->increments('id_sala_adicional');
            $table->integer('reserva_id')->unsigned();
            $table->integer('adicional_id')->unsigned();
            $table->date('fecha_reserva');
            $table->time('hora_reserva');
            $table->timestamps();
            $table->foreign('reserva_id')->references('id_reserva')->on('reserva');
            $table->foreign('adicional_id')->references('id_adicional')->on('adicional');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserva_adicional');
    }
}
