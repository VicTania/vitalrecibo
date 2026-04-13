<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| VitalRecibo API Routes
|--------------------------------------------------------------------------
|
| Rutas API para el sistema de recibos digitales
|
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Cooperaciones
    Route::apiResource('cooperaciones', \App\Http\Controllers\Api\CooperacionController::class)->missing(function () {
        return response()->json(['message' => 'Controller not implemented yet'], 501);
    });

    // Pagos de cooperaciones
    Route::apiResource('pagos-cooperaciones', \App\Http\Controllers\Api\PagoCooperacionController::class)->missing(function () {
        return response()->json(['message' => 'Controller not implemented yet'], 501);
    });

    // Recibos
    Route::apiResource('recibos', \App\Http\Controllers\Api\ReciboController::class)->missing(function () {
        return response()->json(['message' => 'Controller not implemented yet'], 501);
    });

    // Reportes de recibos
    Route::get('reportes/recibos/{tipo}', function ($tipo) {
        return response()->json(['message' => 'Reportes not implemented yet'], 501);
    })->name('reportes.recibos');

});

// Rutas públicas (sin autenticación)
Route::prefix('public')->group(function () {

    // Validación de recibos
    Route::post('recibos/validar', function (Request $request) {
        return response()->json([
            'valido' => false,
            'mensaje' => 'Validación pública en desarrollo'
        ]);
    })->name('api.recibos.validar');

});