<?php

namespace Nh\NhFileManager;

use Illuminate\Support\ServiceProvider;

class NhFileManagerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nhFileManager');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/nh-file-manager'),
        ]);

        if (! $this->app->runningInConsole()) {
            return;
        }

//        $this->commands([
//            Console\InstallCommand::class,
//        ]);
    }
}
