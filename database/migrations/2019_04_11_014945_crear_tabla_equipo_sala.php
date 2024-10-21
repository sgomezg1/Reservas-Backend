<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaEquipoSala extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipos_sala', function (Blueprint $table) {
            $table->increments('id_equipos_sala');
            $table->integer('sala_pertenece')->unsigned();
            $table->string('img_equipo');
            $table->boolean('estado_sala');
            $table->string('nom_equipo');
            $table->foreign('sala_pertenece')->references('id_sala')->on('sala');
        });  
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipos_sala', function($table) {
            $table->dropForeign(['sala_pertenece']);
            $table->dropColumn('sala_pertenece');
        });
        Schema::drop("equipos_sala");
    }
}
