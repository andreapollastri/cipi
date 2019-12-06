<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->bigIncrements('id')->unique();
            $table->text('name');
            $table->text('provider')->nullable();
            $table->text('location')->nullable();
            $table->text('ip');
            $table->integer('port');
            $table->text('username');
            $table->text('password');
            $table->text('dbroot');
            $table->integer('complete')->default('0');
            $table->text('servercode');
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
        Schema::dropIfExists('servers');
    }
}
