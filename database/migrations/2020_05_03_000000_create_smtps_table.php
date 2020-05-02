<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmtpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smtps', function (Blueprint $table) {
            $table->id();
            $table->string('host')->nullable()->default('smtp.yourdomain.ltd');
            $table->string('port')->nullable()->default('25');
            $table->string('from')->nullable()->default('you@yourdomain.ltd');
            $table->string('encryption')->nullable()->default('ssl');
            $table->string('username')->nullable()->default('yourid');
            $table->string('password')->nullable()->default('secret');
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
        Schema::dropIfExists('smtps');
    }
}
