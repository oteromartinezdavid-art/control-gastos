<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Configuracion;
use App\Services\MarketData\MarketDataProvider;
use App\Services\MarketData\FinnhubProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Proveedor de datos de mercado. Resuelto por petición: lee la API key
        // del usuario autenticado desde Configuración. Cambiar de proveedor
        // (FMP, Sheets...) es cambiar solo esta línea.
        $this->app->bind(MarketDataProvider::class, function () {
            $apiKey = auth()->check()
                ? Configuracion::obtener(auth()->id(), 'finnhub_api_key')
                : null;

            return new FinnhubProvider($apiKey);
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
