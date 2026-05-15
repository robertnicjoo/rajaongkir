<?php

use Illuminate\Support\Facades\Route;
use Nicxonsolutions\Rajaongkir\Http\Controllers\RajaongkirController;

Route::get('destinations/domestic', [RajaongkirController::class, 'domesticDestinations']);
Route::get('destinations/international', [RajaongkirController::class, 'internationalDestinations']);
Route::post('costs/{zone}', [RajaongkirController::class, 'costs'])
    ->whereIn('zone', ['domestic', 'international']);
