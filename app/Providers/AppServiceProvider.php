<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
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
        Model::preventLazyLoading(!app()->isProduction());

        \Illuminate\Support\Facades\View::composer('layouts.header', function ($view) {
            $low_quantity_products = \Modules\Product\Entities\Product::select('id', 'product_quantity', 'product_stock_alert', 'product_code')
                ->whereColumn('product_quantity', '<=', 'product_stock_alert')
                ->get();
            $view->with('low_quantity_products', $low_quantity_products);
        });
    }
}
