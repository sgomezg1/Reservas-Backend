<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablaDescuentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('descuentos', function (Blueprint $table) {
            $table->increments('id_descuento');
            $table->integer('valor_descuento');
            $table->integer('dia_descuento');
            $table->integer('tipo_descuento');
            $table->date('fecha_descuento');
            $table->date('fecha_fin_descuento');
            $table->time('hora_inicio_descuento');            
            $table->time('hora_fin_descuento');            
            $table->boolean('estado_descuento');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('descuentos');
    }
}
