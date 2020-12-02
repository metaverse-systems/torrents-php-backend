<?php

namespace MetaverseSystems\TorrentPHPBackend;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use MetaverseSystems\TorrentPHPBackend\Commands\CheckTorrent;

class TorrentPHPBackendProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            CheckTorrent::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if(!$this->app->routesAreCached())
        {
            require __DIR__.'/Routes.php';
        }

        $this->loadMigrationsFrom(__DIR__.'/Migrations');

        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->command('torrent:check')->everyMinute();
        });
    }
}
