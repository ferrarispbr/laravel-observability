<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laravel + Grafana — Observabilidade</title>

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="min-vh-100">
    <header class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <span id="application-brand" class="navbar-brand mb-0 h1">
                Laravel + Grafana
            </span>

            <span id="authenticated-user" class="d-none align-items-center gap-2 border border-success rounded px-3 py-1 text-success fw-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Z"/>
                    <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                </svg>

                <span id="authenticated-user-name"></span>
            </span>

            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-primary"> Interface de demonstração  </span>
                <button id="logout-button" type="button" class="btn btn-outline-light btn-sm d-none" > Sair </button>
            </div>
        </div>
    </header>

    @yield('content')
</body>
</html>
