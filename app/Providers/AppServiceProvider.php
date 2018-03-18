<?php

namespace App\Providers;

use App\Requests\Request;
use App\Requests\RequestObserver;
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
        Request::observe(RequestObserver::class);

        //DB::listen(function ($query) {
        //    dump([$query->sql, $query->bindings, $query->time]);
        //});
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
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
