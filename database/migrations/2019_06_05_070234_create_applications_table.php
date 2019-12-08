<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('domain');
            $table->bigInteger('server_id')->unsigned()->index();
            $table->text('username');
            $table->text('password');
            $table->text('dbpass');
            $table->text('appcode');
            $table->text('basepath')->nullable();
            $table->timestamps();
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('server_id', 'applications_server_id_foreign')
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
        Schema::dropIfExists('applications');
    }
}
