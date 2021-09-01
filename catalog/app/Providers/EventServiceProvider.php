<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Gender;
use App\Observers\CategoryObserver;
use App\Observers\GenderObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    public function boot()
    {
        Category::observe(CategoryObserver::class);
        Gender::observe(GenderObserver::class);
    }
}
