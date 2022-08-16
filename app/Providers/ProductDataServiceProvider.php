<?php

namespace App\Providers;

use App\Libraries\ProductData;
use Illuminate\Support\ServiceProvider;

class ProductDataServiceProvider extends ServiceProvider
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
        $this->app->bind('App\Libraries\ProductData', function ($app) {
            return new ProductData();
        });
    }
}
