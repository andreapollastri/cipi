<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAliasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('aliases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('domain');
            $table->bigInteger('application_id')->unsigned()->index();
            $table->bigInteger('server_id')->unsigned()->index();
            $table->text('aliascode');
            $table->timestamps();
        });


        Schema::table('aliases', function (Blueprint $table) {
            $table->foreign('application_id', 'alias_application_id_foreign')
                ->references('id')
                ->on('applications')
                ->onDelete('cascade');
        });


        Schema::table('aliases', function (Blueprint $table) {
            $table->foreign('server_id', 'alias_server_id_foreign')
                ->references('id')
                ->on('servers')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aliases');
    }
}
