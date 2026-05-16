<?php

use Illuminate\Support\Facades\Route;
use Nicxonsolutions\Rajaongkir\Http\Controllers\RajaongkirController;

Route::get('destinations/domestic', [RajaongkirController::class, 'domesticDestinations']);
Route::get('destinations/international', [RajaongkirController::class, 'internationalDestinations']);
Route::get('destinations/provinces', [RajaongkirController::class, 'provinces']);
Route::get('destinations/provinces/{provinceId}', [RajaongkirController::class, 'province']);
Route::get('destinations/cities/{provinceId}', [RajaongkirController::class, 'cities']);
Route::get('destinations/cities/{provinceId}/{cityId}', [RajaongkirController::class, 'city']);
Route::get('destinations/districts/{cityId}', [RajaongkirController::class, 'districts']);
Route::get('destinations/districts/{cityId}/{districtId}', [RajaongkirController::class, 'district']);
Route::get('destinations/sub-districts/{districtId}', [RajaongkirController::class, 'subDistricts']);
Route::get('destinations/sub-districts/{districtId}/{subDistrictId}', [RajaongkirController::class, 'subDistrict']);
Route::post('costs/{zone}', [RajaongkirController::class, 'costs'])
    ->where('zone', 'domestic|international');
Route::post('costs/district/domestic', [RajaongkirController::class, 'districtDomesticCosts']);
Route::post('track/waybill', [RajaongkirController::class, 'trackWaybill']);
