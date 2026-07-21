<?php

namespace App\Providers;

use App\Services\Ai\AiClient;
use App\Services\Integration\ZaiKpiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ZaiKpiClient has scalar constructor args (base URL + token), so the
        // container can't autowire it — build it from config for injection
        // into the KPI controller and the SyncKpiToZaiKpi job.
        $this->app->bind(ZaiKpiClient::class, fn () => ZaiKpiClient::fromConfig());
        $this->app->bind(AiClient::class, fn () => AiClient::fromConfig());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}


