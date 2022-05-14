<?php

namespace Allegro\Socialite;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Provider as ProviderContract;
use Laravel\Socialite\Facades\Socialite;

class AllegroSocialiteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->bindSocialiteProvider();
    }

    /**
     * Bind the Allegro Socialite provider.
     *
     * @return void
     */
    protected function bindSocialiteProvider(): void
    {
        Socialite::extend('allegro', function (Application $app): ProviderContract {
            return Socialite::buildProvider(AllegroProvider::class, $app['config']['services']['allegro']);
        });
    }
}
