@extends('layouts.app')

@section('content')
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <section class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h3 mb-2"> Entrar na aplicação </h1>

                        <p class="text-body-secondary mb-4">
                            Informe suas credenciais para acessar os produtos e pedidos.
                        </p>

                        <form id="login-form">
                            <div class="mb-3">
                                <label for="login-email" class="form-label"> E-mail </label>
                                <input id="login-email" value= "interface.teste@exemplo.com" type="email" class="form-control" autocomplete="email" required >
                            </div>

                            <div class="mb-4">
                                <label for="login-password" class="form-label"> Senha </label>
                                <input id="login-password" value="Teste@12345" type="password" class="form-control" autocomplete="current-password" required >
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Entrar
                            </button>
                        </form>

                        <div id="login-feedback" class="mt-3" aria-live="polite"></div>
                    </div>
                </section>
            </div>
        </div>
    </main>
@endsection
