<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\STBController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

});

Route::middleware('auth:sanctum')->prefix('devices')->group(function () {
    Route::get('/', [DeviceController::class, 'getDevices']);
    Route::post('/', [DeviceController::class, 'saveDevice']);
    Route::put('/{id}', [DeviceController::class, 'updateDevice']);
    Route::delete('/{id}', [DeviceController::class, 'deleteDevice']);
});

Route::middleware('auth:sanctum')->prefix('stb')->group(function () {
    Route::get('/stb/poll', [STBController::class, 'stbPoll']);
    Route::get('/stb/config/{id}', [STBController::class, 'stbConfig']);
});

Route::middleware('auth:sanctum')->prefix('contents')->group(function () {
    Route::get('/', [ContentController::class, 'getContents']);
    Route::post('/', [ContentController::class, 'saveContent']);
    Route::put('/{id}', [ContentController::class, 'updateContent']);
    Route::delete('/{id}', [ContentController::class, 'deleteContent']);
    Route::post('/download/{id}', [ContentController::class, 'downloadContent']);
});

Route::middleware('auth:sanctum')->prefix('templates')->group(function () {
    Route::get('/', [TemplateController::class, 'getTemplates']);
    Route::post('/', [TemplateController::class, 'saveTemplate']);
    Route::put('/{id}', [TemplateController::class, 'updateTemplate']);
    Route::delete('/{id}', [TemplateController::class, 'deleteTemplate']);
});

Route::middleware('auth:sanctum')->prefix('analytics')->group(function () {
    Route::post('/ingest', [AnalyticsController::class, 'ingestAnalytics']);
    Route::get('/summary', [AnalyticsController::class, 'getSummary']);
    Route::get('/timeline', [AnalyticsController::class, 'getTimeline']);
    Route::get('/demographics', [AnalyticsController::class, 'getDemographics']);
});