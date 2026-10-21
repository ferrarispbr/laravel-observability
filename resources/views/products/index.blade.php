@extends('layouts.app')

@section('content')
    <main id="products-page" class="container py-5">>
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2"> Produtos e pedidos </h1>

                <p class="text-body-secondary mb-0"> Selecione produtos para montar um novo pedido. </p>
            </div>

            <button id="logout-button" type="button" class="btn btn-outline-secondary">
                Sair
            </button>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <section class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3"> Produtos disponíveis </h2>

                        <div id="products-feedback" aria-live="polite"></div>

                        <div id="products-list" class="row g-3">
                            <div class="col-12">
                                <p class="text-body-secondary mb-0"> Os produtos serão carregados aqui. </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="col-lg-4">
                <section class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3"> Pedido atual </h2>

                        <div id="cart-items">
                            <p class="text-body-secondary mb-0"> Nenhum produto selecionado. </p>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between fw-semibold mb-3">
                            <span>Total</span>
                            <span id="cart-total">R$ 0,00</span>
                        </div>

                        <button id="create-order-button" type="button" class="btn btn-primary w-100" disabled>
                            Criar pedido
                        </button>

                        <div id="order-feedback" class="mt-3" aria-live="polite" ></div>
                    </div>
                </section>
            </aside>
        </div>
    </main>
@endsection
