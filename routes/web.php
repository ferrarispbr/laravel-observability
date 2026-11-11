<?php

    use App\Http\Controllers\Api\AuthController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\MetricsController;

    Route::view('/', 'auth.login')->middleware('guest')->name('login');

    Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('login.store');

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    Route::middleware('auth')->group(function () {
        Route::view('/produtos', 'products.index')->name('products.index');

        Route::view('/meus-pedidos', 'orders.index')->name('orders.index');

        Route::view('/pedidos/{order}', 'orders.show')->name('orders.show');
    });

    # rota do prometheus
    Route::get('/metrics', MetricsController::class)->middleware('prometheus.token')->name('metrics');
