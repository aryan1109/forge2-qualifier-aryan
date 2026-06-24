<?php

use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\ListController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['ok' => true]));

Route::get('/boards/default', [BoardController::class, 'defaultBoard']);
Route::apiResource('boards', BoardController::class)
    ->only(['index', 'store', 'show', 'update', 'destroy']);

Route::post('/boards/{board}/lists', [ListController::class, 'store']);
Route::patch('/lists/{listModel}', [ListController::class, 'update']);
Route::delete('/lists/{listModel}', [ListController::class, 'destroy']);

Route::post('/lists/{listModel}/cards', [CardController::class, 'store']);
Route::patch('/cards/{card}', [CardController::class, 'update']);
Route::post('/cards/{card}/move', [CardController::class, 'move']);
Route::delete('/cards/{card}', [CardController::class, 'destroy']);
