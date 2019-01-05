<?php

namespace App\Providers;

use App\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Storage\RequestStore::class, Storage\Redis\RequestStore::class);
        $this->app->bind(Storage\TokenStore::class, Storage\Redis\TokenStore::class);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            Storage\RequestStore::class,
            Storage\TokenStore::class
        ];
    }
}
