<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use L5Swagger\L5Swagger;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Schema::defaultStringLength(190);

        // if ($this->app->environment('local')) {
        //     L5Swagger::asset(storage_path('api-docs/swagger.json')); // Point to your Swagger JSON file
        // }
    }
}
