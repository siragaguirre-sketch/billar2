<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/juegos'));

Route::get('/juegos', [GameController::class, 'index'])->name('juegos.index');
Route::post('/juegos', [GameController::class, 'store'])->name('juegos.store');
Route::get('/juego/{id}', [GameController::class, 'show'])->name('juegos.show');
Route::post('/juego/{id}/unirse', [GameController::class, 'join'])->name('juegos.join');
Route::post('/juego/{id}/tiro', [GameController::class, 'shot'])->name('juegos.shot');
Route::post('/juego/{id}/resultado', [GameController::class, 'settle'])->name('juegos.settle');
Route::post('/juego/{id}/chat', [GameController::class, 'chat'])->name('juegos.chat');
Route::post('/juego/{id}/eliminar', [GameController::class, 'destroy'])->name('juegos.destroy');
Route::get('/historial', [GameController::class, 'history'])->name('juegos.history');
Route::get('/ranking', [GameController::class, 'leaderboard'])->name('juegos.leaderboard');
