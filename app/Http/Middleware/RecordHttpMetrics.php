<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Prometheus\CollectorRegistry;
use Symfony\Component\HttpFoundation\Response;

class RecordHttpMetrics
{
    public function __construct(
        private readonly CollectorRegistry $registry,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('metrics')) {
            return $next($request);
        }

        $startedAt = microtime(true);

        $response = $next($request);

        $route = $request->route()?->uri() ?? 'unknown';
        $method = $request->method();
        $status = (string) $response->getStatusCode();
        $duration = microtime(true) - $startedAt;

        $requestCounter = $this->registry->getOrRegisterCounter(
            'laravel',
            'http_requests_total',
            'Total de requisições HTTP processadas pela aplicação.',
            ['method', 'route', 'status'],
        );

        $requestCounter->inc([
            $method,
            $route,
            $status,
        ]);

        $requestDuration = $this->registry->getOrRegisterHistogram(
            'laravel',
            'http_request_duration_seconds',
            'Tempo de resposta das requisições HTTP em segundos.',
            ['method', 'route'],
            [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5],
        );

        $requestDuration->observe($duration, [
            $method,
            $route,
        ]);

        return $response;
    }
}
