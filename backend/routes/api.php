<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PlaylistItemController;
use App\Http\Controllers\LayoutController;
use App\Http\Controllers\LayoutItemController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScheduleDeviceController;
use App\Http\Controllers\SystemLogController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Contents CRUD
Route::middleware('auth:sanctum')->prefix('contents')->group(function () {
    Route::get('/', [ContentController::class, 'index']);
    Route::get('/{id}', [ContentController::class, 'show']);
    Route::post('/', [ContentController::class, 'store']);
    Route::put('/{id}', [ContentController::class, 'update']);
    Route::delete('/{id}', [ContentController::class, 'destroy']);
});

// Playlists CRUD
Route::middleware('auth:sanctum')->prefix('playlists')->group(function () {
    Route::get('/', [PlaylistController::class, 'index']);
    Route::get('/{id}', [PlaylistController::class, 'show']);
    Route::post('/', [PlaylistController::class, 'store']);
    Route::put('/{id}', [PlaylistController::class, 'update']);
    Route::delete('/{id}', [PlaylistController::class, 'destroy']);
});

// Playlist Items CRUD
Route::middleware('auth:sanctum')->prefix('playlist-items')->group(function () {
    Route::get('/', [PlaylistItemController::class, 'index']);
    Route::get('/{id}', [PlaylistItemController::class, 'show']);
    Route::post('/', [PlaylistItemController::class, 'store']);
    Route::put('/{id}', [PlaylistItemController::class, 'update']);
    Route::delete('/{id}', [PlaylistItemController::class, 'destroy']);
    Route::get('/playlist/{playlistId}', [PlaylistItemController::class, 'getByPlaylist']);
});

// Layouts CRUD
Route::middleware('auth:sanctum')->prefix('layouts')->group(function () {
    Route::get('/', [LayoutController::class, 'index']);
    Route::get('/{id}', [LayoutController::class, 'show']);
    Route::post('/', [LayoutController::class, 'store']);
    Route::put('/{layout}', [LayoutController::class, 'update']);
    Route::delete('/{id}', [LayoutController::class, 'destroy']);
});

// Layout Items CRUD
Route::middleware('auth:sanctum')->prefix('layout-items')->group(function () {
    Route::get('/', [LayoutItemController::class, 'index']);
    Route::get('/{id}', [LayoutItemController::class, 'show']);
    Route::post('/', [LayoutItemController::class, 'store']);
    Route::put('/{id}', [LayoutItemController::class, 'update']);
    Route::delete('/{id}', [LayoutItemController::class, 'destroy']);
    Route::get('/layout/{layoutId}', [LayoutItemController::class, 'getByLayout']);
});

// Schedules CRUD
Route::middleware('auth:sanctum')->prefix('schedules')->group(function () {
    Route::get('/', [ScheduleController::class, 'index']);
    Route::get('/{id}', [ScheduleController::class, 'show']);
    Route::post('/', [ScheduleController::class, 'store']);
    Route::put('/{id}', [ScheduleController::class, 'update']);
    Route::delete('/{id}', [ScheduleController::class, 'destroy']);
    Route::patch('/{id}/status', [ScheduleController::class, 'updateStatus']);
});

// Schedule Devices CRUD
Route::middleware('auth:sanctum')->prefix('schedule-devices')->group(function () {
    Route::get('/', [ScheduleDeviceController::class, 'index']);
    Route::get('/{id}', [ScheduleDeviceController::class, 'show']);
    Route::post('/', [ScheduleDeviceController::class, 'store']);
    Route::delete('/{id}', [ScheduleDeviceController::class, 'destroy']);
    Route::get('/schedule/{scheduleId}', [ScheduleDeviceController::class, 'getBySchedule']);
    Route::get('/device/{deviceId}', [ScheduleDeviceController::class, 'getByDevice']);
});

// Devices CRUD
Route::middleware('auth:sanctum')->prefix('devices')->group(function () {
    Route::get('/', [DeviceController::class, 'index']);
    Route::get('/{device}', [DeviceController::class, 'show']);
    Route::post('/', [DeviceController::class, 'store']);
    Route::put('/{device}', [DeviceController::class, 'update']);
    Route::delete('/{device}', [DeviceController::class, 'destroy']);
});

// Analytics Logs CRUD
Route::middleware('auth:sanctum')->prefix('analytics-logs')->group(function () {
    Route::get('/', [AnalyticsController::class, 'index']);
    Route::get('/{id}', [AnalyticsController::class, 'show']);
    Route::post('/', [AnalyticsController::class, 'store']);
    Route::put('/{id}', [AnalyticsController::class, 'update']);
    Route::delete('/{id}', [AnalyticsController::class, 'destroy']);
    // Existing analytics routes
    Route::post('/ingest', [AnalyticsController::class, 'ingestAnalytics']);
    Route::get('/summary', [AnalyticsController::class, 'getSummary']);
    Route::get('/timeline', [AnalyticsController::class, 'getTimeline']);
    Route::get('/demographics', [AnalyticsController::class, 'getDemographics']);
});

// System Logs CRUD
Route::middleware('auth:sanctum')->prefix('system-logs')->group(function () {
    Route::get('/', [SystemLogController::class, 'index']);
    Route::get('/{id}', [SystemLogController::class, 'show']);
    Route::post('/', [SystemLogController::class, 'store']);
    Route::put('/{id}', [SystemLogController::class, 'update']);
    Route::delete('/{id}', [SystemLogController::class, 'destroy']);
});

