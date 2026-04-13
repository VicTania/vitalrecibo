<?php

namespace Kaely\VitalRecibo;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;

class VitalReciboServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/vitalrecibo.php', 'vitalrecibo'
        );
    }

    public function boot(): void
    {
        // Publicar configuración
        $this->publishes([
            __DIR__.'/../config/vitalrecibo.php' => config_path('vitalrecibo.php'),
        ], 'vitalrecibo-config');

        // Cargar migraciones
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Registrar assets de Filament
        if (class_exists(FilamentAsset::class)) {
            FilamentAsset::register([
                Css::make('vitalrecibo-styles', __DIR__ . '/../resources/dist/vitalrecibo.css'),
                Js::make('vitalrecibo-scripts', __DIR__ . '/../resources/dist/vitalrecibo.js'),
            ], 'kaely/vitalrecibo');
        }

        // Cargar vistas
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'vitalrecibo');

        // Publicar vistas
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/vitalrecibo'),
        ], 'vitalrecibo-views');

        // Cargar rutas
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
    }
}