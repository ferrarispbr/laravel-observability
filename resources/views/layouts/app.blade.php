<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Laravel + Grafana — Observabilidade</title>
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
    </head>
    <body class="min-vh-100">
        <div id="application-shell">
            <header class="navbar navbar-dark bg-dark shadow-sm">
                <div class="container">
                    <span id="application-brand" class="navbar-brand mb-0 h1">Laravel + Grafana</span>
                    <span id="authenticated-user" class="d-none align-items-center gap-2 border border-success rounded px-3 py-1 text-success fw-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" >
                            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Z"/>
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                        </svg>
                        <span id="authenticated-user-name"></span>
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge text-bg-primary"> Interface de demonstração </span>
                        <button id="logout-button" type="button" class="btn btn-outline-light btn-sm d-none"> Sair </button>
                    </div>
                </div>
            </header>
            @yield('content')
        </div>

        <div id="session-expired-overlay" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 align-items-center justify-content-center p-3" role="alertdialog" aria-modal="true" aria-labelledby="session-expired-title" aria-describedby="session-expired-message"  style="z-index: 2000;">
            <section class="card border-0 shadow-lg w-100" style="max-width: 30rem;">
                <div class="card-body p-4 text-center">
                    <div class="spinner-border text-warning mb-3" role="status" aria-label="Redirecionando para o login"></div>
                    <h2 id="session-expired-title" class="h4 mb-3"> Sessão expirada</h2>
                    <p id="session-expired-message" class="mb-2"> Para proteger sua conta, esta tela foi bloqueada. </p>
                    <p class="text-body-secondary mb-0"> Você será direcionado para o login em alguns segundos.</p>
                </div>
            </section>
        </div>
    </body>
</html>
