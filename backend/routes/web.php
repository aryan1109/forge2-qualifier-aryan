<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'Forge 2 Kanban API',
    'health' => '/api/health',
]));
