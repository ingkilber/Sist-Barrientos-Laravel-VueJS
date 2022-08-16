<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Libraries\imageHandler;

class imageHandleProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Libraries\imageHandler', function ($app) {
            return new imageHandler();
        });
    }
}
