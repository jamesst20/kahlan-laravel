<?php
namespace Jamesst20\KahlanLaravel;

use Illuminate\Support\ServiceProvider;
use Jamesst20\KahlanLaravel\Console\KahlanRunCommand;

class KahlanLaravelServiceProvider extends ServiceProvider
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
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                KahlanRunCommand::class
            ]);
        }
    }
}
