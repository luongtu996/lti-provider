<?php

namespace App\Providers;

use App\Lti\Lti13Cache;
use App\Lti\Lti13Cookie;
use App\Lti\Lti13Database;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Packback\Lti1p3\Interfaces\ICache;
use Packback\Lti1p3\Interfaces\ICookie;
use Packback\Lti1p3\Interfaces\IDatabase;
use Packback\Lti1p3\Interfaces\ILtiServiceConnector;
use Packback\Lti1p3\LtiServiceConnector;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ICache::class, Lti13Cache::class);
        $this->app->bind(ICookie::class, Lti13Cookie::class);
        $this->app->bind(IDatabase::class, Lti13Database::class);
        // As of version 3.0
        $this->app->bind(ILtiServiceConnector::class, function () {
            return new LtiServiceConnector(app(ICache::class), new Client([
                'timeout' => 30,
            ]));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
