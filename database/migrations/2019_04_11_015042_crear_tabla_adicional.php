<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaAdicional extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adicional', function (Blueprint $table) {
            $table->increments('id_adicional');
            $table->string('nom_adicional');
            $table->integer('cant_adicional');
            $table->integer('precio_adicional');
            $table->boolean('estado_adicional');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("adicional");
    }
}
