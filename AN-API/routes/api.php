<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DevicesInNetworkController;
use App\Http\Controllers\InterfaceOfDeviceController;
use App\Http\Controllers\PortController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */

Route::controller(DeviceController::class)->group(function () {
    Route::get('devices/{device}', 'show');
});
Route::controller(PortController::class)->group(function () {
    Route::get('ports', 'index');
    Route::get('ports/devicesPorts/{device}', 'devicesPorts');
});
Route::controller(DevicesInNetworkController::class)->group(function () {
    Route::get('devices_in_networks', 'index');
    Route::post('devices_in_networks', 'store');
    Route::get('devices_in_networks/{device}', 'show');
    Route::get('devices_in_networks/findDeviceType/{type}', 'findDeviceType');
    Route::delete('devices_in_networks/delete', 'delete');
});
Route::controller(InterfaceOfDeviceController::class)->group(function () {
    Route::get('interface_of_devices', 'index');
    Route::delete('interface_of_devices/delete', 'delete');
});
