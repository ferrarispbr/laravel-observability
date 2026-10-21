<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.login')->name('login');

Route::view('/produtos', 'products.index')->name('products.index');

Route::view('/meus-pedidos', 'orders.index')->name('orders.index');

Route::view('/pedidos/{order}', 'orders.show')->name('orders.show');
