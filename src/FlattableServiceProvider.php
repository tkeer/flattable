<?php


namespace Tkeer\Flattable;

use Illuminate\Support\ServiceProvider;
use Tkeer\Flattable\Commands\FillFlattableCommand;

class FlattableServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/flattable.php', 'flattable');
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/flattable.php' => config_path('flattable.php'),
            ], 'config');

            $this->commands([
                FillFlattableCommand::class
            ]);

        }
    }
}