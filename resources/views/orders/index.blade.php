@extends('layouts.app')

@section('content')
    <main id="orders-page" class="container py-5">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2">Meus pedidos</h1>

                <p class="text-body-secondary mb-0">
                    Consulte os pedidos que você criou.
                </p>
            </div>

            <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                Continuar comprando
            </a>
        </div>

        <section class="card shadow-sm">
            <div class="card-body p-4">
                <div id="orders-feedback" aria-live="polite"></div>

                <div id="orders-list">
                    <p class="text-body-secondary mb-0">
                        Os pedidos serão carregados aqui.
                    </p>
                </div>
            </div>
        </section>
    </main>
@endsection
