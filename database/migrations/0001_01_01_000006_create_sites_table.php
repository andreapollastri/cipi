<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('domain')->index()->unique();
            $table->string('username');
            $table->string('password');
            $table->string('basepath')->nullable();
            $table->string('repository')->nullable();
            $table->string('branch')->nullable();
            $table->string('php');
            $table->text('supervisor')->nullable();
            $table->text('nginx')->nullable();
            $table->text('deploy')->nullable();
            $table->text('crontab')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
