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
            $table->id();
            $table->string('server_id')->unique()->index();
            $table->string('ip');
            $table->string('name');
            $table->string('password');
            $table->string('database');
            $table->string('provider')->nullable();
            $table->string('location')->nullable();
            $table->string('php')->default(config('cipi.default_php'));
            $table->text('github_key')->nullable();
            $table->text('cron')->nullable();
            $table->boolean('default')->default(false);
            $table->integer('build')->nullable();
            $table->integer('status')->default('0');
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
