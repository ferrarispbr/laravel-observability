<?php

    use App\Http\Controllers\Api\AuthController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Api\ProductController;
    use App\Http\Controllers\Api\OrderController;

    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');  // esse erro apontado é somente da  IDE, o laravel ja reconhece o throttle

    Route::get('/user', function (Request $request) {
        return response()->json(
            $request->user()->only([
                'id',
                'name',
                'email',
                'address',
                'address_number',
                'address_complement',
                'neighborhood',
                'city',
                'state',
                'zip_code',
            ]),
        );
    })->middleware('auth:sanctum');

    //Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login'); // esse erro apontado é somente da  IDE, o laravel ja reconhece o throttle

    //Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('/products', [ProductController::class, 'index'])->middleware('auth:sanctum');

    Route::post('/products', [ProductController::class, 'store'])->middleware('auth:sanctum');

    Route::get('/orders', [OrderController::class, 'index'])->middleware('auth:sanctum');

    Route::get('/orders/{order}', [OrderController::class, 'show'])->middleware('auth:sanctum');

    Route::post('/orders', [OrderController::class, 'store'])->middleware('auth:sanctum');

    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm'])->middleware('auth:sanctum');

    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->middleware('auth:sanctum');
