<?php

namespace App\Providers;

use App\Models\{CastMember, Category, Gender};
use App\Observers\UUID;
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
        CastMember::observe(UUID::class);
        Category::observe(UUID::class);
        Gender::observe(UUID::class);
    }
}
