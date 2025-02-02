<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/book', [BookController::class, 'search']);

Route::post('/reservations', [ReservationController::class, 'set']);

require __DIR__.'/auth.php';
