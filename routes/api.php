<?php

use App\Http\Controllers\UploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('uploads', UploadController::class)->only(['store', 'show', 'index']);
Route::post('uploads/{upload}/reprocess', [UploadController::class, 'reprocess']);