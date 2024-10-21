<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablaCorreosPorUsuario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correos_usuario', function (Blueprint $table) {
            $table->increments('id_correo_usuario');
            $table->string('dir_correo');
            $table->integer('id_usuario_pert')->unsigned();
            $table->foreign('id_usuario_pert')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('correos_usuario');
    }
}
