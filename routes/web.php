<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| VitalRecibo Web Routes
|--------------------------------------------------------------------------
|
| Rutas web para validación pública de recibos y otras funcionalidades
|
*/

// Validación pública de recibos
Route::get('/recibos/validar/{numero}', function ($numero) {
    $hash = request('hash');

    // Aquí iría la lógica de validación
    // Por ahora retornamos una vista simple

    return response()->json([
        'valido' => false,
        'mensaje' => 'Funcionalidad en desarrollo'
    ]);
})->name('recibos.validar');

// Vista pública del recibo
Route::get('/recibos/ver/{numero}', function ($numero) {
    $hash = request('hash');

    // Validar hash y mostrar recibo público

    return response()->json([
        'mensaje' => 'Vista de recibo en desarrollo'
    ]);
})->name('recibos.ver');