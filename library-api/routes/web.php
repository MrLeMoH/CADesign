<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;


Route::get('/book', [BookController::class, 'search']);

Route::delete('/book/{id}', [BookController::class, 'delete'])->middleware('auth');
Route::patch('/book/{id}', [BookController::class, 'edit'])->middleware('auth');
Route::post('/book', [BookController::class, 'create'])->middleware('auth');


Route::post('/reservations', [ReservationController::class, 'set']);



require __DIR__.'/auth.php';
