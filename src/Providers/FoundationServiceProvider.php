<?php

declare(strict_types=1);

namespace Manzadey\OrchidMediaLibrary\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Manzadey\OrchidMediaLibrary\Console\Commands\InstallCommand;

class FoundationServiceProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->commands([
            InstallCommand::class,
        ]);
    }

    public function boot() : void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'orchid-laravel-media-library');

        $this->publishes([
            $this->path('stubs/routes') => base_path('routes/platform'),
            $this->path('stubs/app')    => app_path(),
        ], 'orchid-media-library-stubs');

        $this->publishes([
            $this->path('stubs/routes') => base_path('routes/platform'),
        ], 'routes-stubs');

        $this->publishes([
            $this->path('stubs/app') => app_path(),
        ], 'screens-stubs');
    }

    private function path(string $path) : string
    {
        return __DIR__ . '/../..' . Str::start($path, '/');
    }
}
