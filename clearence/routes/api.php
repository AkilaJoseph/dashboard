<?php

use App\Http\Controllers\Api\PushSubscriptionController;
use App\Http\Controllers\Api\StudentQrController;
use App\Http\Controllers\Api\DepartmentScanController;
use App\Http\Controllers\Api\ClearancePredictionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('v1')->group(function () {
    Route::post('/push/subscribe',   [PushSubscriptionController::class, 'subscribe']);
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe']);

    Route::get('/student/qr-token',   [StudentQrController::class,    'token'])->name('api.student.qr-token');
    Route::post('/department/scan',   [DepartmentScanController::class,'scan'])->name('api.department.scan');

    Route::get('/student/requests/{clearance}/prediction', [ClearancePredictionController::class, 'show'])
        ->name('api.student.clearance.prediction');
});
