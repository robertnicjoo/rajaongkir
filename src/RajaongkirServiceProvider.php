<?php

namespace Nicxonsolutions\Rajaongkir;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Nicxonsolutions\Rajaongkir\Api\Client;

class RajaongkirServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/rajaongkir.php', 'rajaongkir');

        $this->app->singleton(Client::class, fn () => new Client(config('rajaongkir')));
        $this->app->singleton(Rajaongkir::class, fn ($app) => new Rajaongkir(
            $app->make(Client::class),
            config('rajaongkir')
        ));
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/rajaongkir.php' => config_path('rajaongkir.php'),
        ], 'rajaongkir-config');

        if (config('rajaongkir.routes.enabled')) {
            Route::middleware(config('rajaongkir.routes.middleware', ['api']))
                ->prefix(config('rajaongkir.routes.prefix', 'api/rajaongkir'))
                ->group(__DIR__ . '/../routes/api.php');
        }
    }
}
