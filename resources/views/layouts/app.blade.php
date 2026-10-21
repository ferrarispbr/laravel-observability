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
            <span class="navbar-brand mb-0 h1">
                Laravel + Grafana
            </span>

            <span class="badge text-bg-primary">
                Interface de demonstração
            </span>
        </div>
    </header>

    @yield('content')
</body>
</html>
