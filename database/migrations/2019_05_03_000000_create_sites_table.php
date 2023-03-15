<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('site_id')->unique()->index();
            $table->bigInteger('server_id')->unsigned();
            $table->string('domain');
            $table->string('username')->unique()->index();
            $table->string('password');
            $table->string('database');
            $table->string('basepath')->nullable()->default('/public');
            $table->string('repository')->nullable();
            $table->string('branch')->nullable();
            $table->string('php')->default(config('cipi.default_php'));
            $table->text('supervisor')->nullable();
            $table->text('nginx')->nullable();
            $table->text('deploy')->nullable();
            $table->boolean('panel')->default(false);
            $table->timestamps();
            $table->index(['server_id', 'domain']);
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->foreign('server_id', 'sites_server_id_foreign')
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
        Schema::dropIfExists('sites');
    }
}
