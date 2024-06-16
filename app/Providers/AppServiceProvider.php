<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Google\Provider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{

    public function register()
    {
    }

    public function boot()
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('google', Provider::class);
        });
    }
}
