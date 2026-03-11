<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Storage;

Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    
    if (!file_exists($fullPath)) {
        abort(404);
    }
    
    return response()->file($fullPath, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET',
        'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization',
    ]);
})->where('path', '.*');