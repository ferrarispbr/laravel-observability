<?php

namespace App\Observability;

use Prometheus\CollectorRegistry;
use Throwable;

class OrderMetrics
{
    public function __construct(private readonly CollectorRegistry $registry, )
    {

    }

    public function recordCreated(): void
    {
        $this->record(function (): void
        {
            $this->registry->getOrRegisterCounter(
                                                    'laravel',
                                                    'orders_created_total',
                                                    'Total de pedidos criados com sucesso.',
                                                )->inc();
        });
    }

    public function recordConfirmed(): void
    {
        $this->record(function (): void
        {
            $this->registry->getOrRegisterCounter(
                                                    'laravel',
                                                    'orders_confirmed_total',
                                                    'Total de pedidos confirmados com sucesso.',
                                                )->inc();
        });
    }

    public function recordCancelled(): void
    {
        $this->record(function (): void
        {
            $this->registry->getOrRegisterCounter(
                                                    'laravel',
                                                    'orders_cancelled_total',
                                                    'Total de pedidos cancelados com sucesso.',
                                                )->inc();
        });
    }

    public function recordConfirmationFailure(string $reason): void
    {
        $this->record(function () use ($reason): void
        {
            $this->registry->getOrRegisterCounter(
                                                    'laravel',
                                                    'order_confirmation_failures_total',
                                                    'Total de tentativas de confirmação de pedido bloqueadas.',
                                                    ['reason'],
                                                )->inc([$reason]);
        });
    }

    private function record(callable $callback): void
    {
        try
        {
            $callback();
        }
        catch (Throwable $exception)
        {
            report($exception);
        }
    }
}
