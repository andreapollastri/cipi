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
            $table->id();
            $table->string('alias_id')->unique()->index();
            $table->bigInteger('site_id')->unsigned();
            $table->string('domain');
            $table->boolean('ssl')->default(false);
            $table->timestamps();
            $table->index(['site_id','domain']);
        });


        Schema::table('aliases', function (Blueprint $table) {
            $table->foreign('site_id', 'aliases_site_id_foreign')
                ->references('id')
                ->on('sites')
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
