<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaReserva extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserva', function (Blueprint $table) {
            $table->increments('id_reserva');
            $table->integer('sala_reserva')->unsigned();
            $table->date('fecha_reserva');
            $table->time('hora_reserva');
            $table->boolean('estado_reserva');
            $table->boolean('recordatorio_enviado');
            $table->integer('total_precio_reserva');
            $table->boolean('estado_confirmacion');
            $table->integer('precio_cobrado');
            $table->integer('id_usuario_reserva')->unsigned();
            $table->foreign('id_usuario_reserva')->references('id')->on('users');
            $table->foreign('sala_reserva')->references('id_sala')->on('sala');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reserva', function($table) {
            $table->dropForeign(['id_usuario_reserva']);
            $table->dropColumn('id_usuario_reserva');
            $table->dropForeign(['sala_reserva']);
            $table->dropColumn('sala_reserva');
        });
        Schema::drop('reserva');
    }
}
