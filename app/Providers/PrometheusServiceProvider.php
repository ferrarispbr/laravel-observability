<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Predis\Client as PredisClient;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Prometheus\Storage\Predis;

class PrometheusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CollectorRegistry::class, function (): CollectorRegistry {
            if ($this->app->environment('testing')) {
                return new CollectorRegistry(new InMemory());
            }

            $client = new PredisClient([
                'scheme' => 'tcp',
                'host' => config('prometheus.redis.host'),
                'port' => config('prometheus.redis.port'),
                'database' => config('prometheus.redis.database'),
            ]);

            return new CollectorRegistry(
                Predis::fromExistingConnection($client),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
