<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\SearchController;

Route::prefix('folders')->group(function () {
    Route::get('/', [FolderController::class, 'index']);
    Route::post('/', [FolderController::class, 'store']);
    Route::delete('/{folder}', [FolderController::class, 'destroy']);
});

Route::prefix('files')->group(function () {
    Route::get('/', [FileController::class, 'index']);
    Route::post('/', [FileController::class, 'store']);
    Route::delete('/{file}', [FileController::class, 'destroy']);
});

Route::prefix('search')->group(function () {
    Route::get('/suggestions', [SearchController::class, 'suggestions']);
    Route::get('/exact', [SearchController::class, 'exact']);
});