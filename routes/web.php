<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/xml/index', [App\Http\Controllers\XmlController::class, 'index']);
Route::post('/xml/upload', [App\Http\Controllers\XmlController::class, 'upload']);
Route::get('/xml/json/{id}', [App\Http\Controllers\XmlController::class, 'getJson']);
