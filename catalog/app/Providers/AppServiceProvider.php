<?php

namespace App\Providers;

use App\Models\{CastMember, Category, Genre, Video};
use App\Observers\UUIDObserver;
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
        CastMember::observe(UUIDObserver::class);
        Category::observe(UUIDObserver::class);
        Genre::observe(UUIDObserver::class);
        Video::observe(UUIDObserver::class);
    }
}
