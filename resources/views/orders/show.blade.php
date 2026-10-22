@extends('layouts.app')

@section('content')
    <main id="order-details-page" class="container py-5">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 mb-2">Detalhes do pedido</h1>

                <p class="text-body-secondary mb-0">
                    Confira os dados e os itens antes de confirmar ou cancelar.
                </p>
            </div>

            <a href="/meus-pedidos" class="btn btn-outline-secondary">
                Voltar para meus pedidos
            </a>
        </div>

        <div id="order-feedback" aria-live="polite"></div>

        <div id="order-details">
            <div id="order-loading" class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 text-body-secondary">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        <span>Carregando pedido...</span>
                    </div>
                </div>
            </div>

            <div id="order-content" class="d-none">
                <div class="row g-4 mb-4">
                    <section class="col-lg-5">
                        <div class="card shadow-sm h-100">
                            <div class="card-body p-4">
                                <h2 class="h5 mb-4">Dados do pedido</h2>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="text-body-secondary small">
                                            Número
                                        </div>

                                        <div id="order-number" class="fw-semibold">
                                            -
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="text-body-secondary small">
                                            Data
                                        </div>

                                        <div id="order-date" class="fw-semibold">
                                            -
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="text-body-secondary small">
                                            Status
                                        </div>

                                        <div>
                                            <span id="order-status" class="badge text-bg-secondary">
                                                -
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="col-lg-7">
                        <div class="card shadow-sm h-100">
                            <div class="card-body p-4">
                                <h2 class="h5 mb-4">Dados do usuário</h2>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="text-body-secondary small">
                                            Nome
                                        </div>

                                        <div id="user-name" class="fw-semibold">
                                            -
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="text-body-secondary small">
                                            Endereço
                                        </div>

                                        <div id="user-address">
                                            -
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="text-body-secondary small">
                                            CEP
                                        </div>

                                        <div id="user-zip-code">
                                            -
                                        </div>
                                    </div>

                                    <div id="user-complement-container" class="col-md-6 d-none">
                                        <div class="text-body-secondary small">
                                            Complemento
                                        </div>

                                        <div id="user-complement">
                                            -
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="text-body-secondary small">
                                            Bairro
                                        </div>

                                        <div id="user-neighborhood">
                                            -
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="text-body-secondary small">
                                            Cidade
                                        </div>

                                        <div id="user-city">
                                            -
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="text-body-secondary small">
                                            Estado
                                        </div>

                                        <div id="user-state">
                                            -
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-4">Itens do pedido</h2>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Item</th>
                                        <th scope="col">Descrição do produto</th>
                                        <th scope="col" class="text-end">Valor unitário</th>
                                        <th scope="col" class="text-center">Quantidade</th>
                                        <th scope="col" class="text-end">Valor total</th>
                                    </tr>
                                </thead>

                                <tbody id="order-items"></tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end pt-4">
                            <div class="text-end">
                                <div class="text-body-secondary">
                                    Total do pedido
                                </div>

                                <div id="order-total" class="fs-4 fw-semibold">
                                    R$ 0,00
                                </div>
                            </div>
                        </div>

                        <div
                            id="order-actions"
                            class="d-flex justify-content-end gap-2 pt-4"
                        ></div>
                    </div>
                </section>
            </div>
        </div>
    </main>
@endsection
