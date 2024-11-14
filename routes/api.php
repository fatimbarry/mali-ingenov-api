<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\TacheController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::middleware('api')->group(function () {
    Route::post('/login', [AuthController::class,'store'])->name('auth.store');
//    Route::controller(UserController::class)->group(function () {
//        Route::get('/users', 'index');
//        Route::post('/users/store', 'store');
//        Route::get('/users/show/{id}', 'show');
//        Route::put('/users/update/{id}', 'update');
//        Route::delete('/users/delete/{id}', 'destroy');
//        Route::get('/users/getuser', 'getUser');
//    });
});





Route::middleware('auth:sanctum')->group(function () {
    Route::put('/user/update-profile', [AuthController::class, 'updateProfile']);

    Route::get('/admin/punches', [PointageController::class, 'adminIndex']);
    Route::get('/punches', [PointageController::class, 'index']);
    Route::post('/punch', [PointageController::class, 'store']);
    Route::get('/punch-status/{employee}', [PointageController::class, 'getPunchStatus']);

    Route::get('taches', [TacheController::class, 'index']);
    Route::middleware(['auth:sanctum', 'chef_de_projet'])->group(function () {
        Route::post('taches', [TacheController::class, 'store']);
        Route::put('taches/{id}', [TacheController::class, 'update']);
        Route::delete('taches/{id}', [TacheController::class, 'destroy']);
    });



    Route::controller(AuthController::class)->group(function () {
        Route::post('logout','logout');
        Route::post('refresh', 'refresh');

    });

    Route::controller(UserController::class)->group(function () {
        Route::get('/users', 'index');
        Route::post('/users/store', 'store');
        Route::get('/users/show/{id}', 'show');
        Route::put('/users/update/{id}', 'update');
        Route::delete('/users/delete/{id}', 'destroy');
        Route::get('/users/getuser', 'getUser');
    });

    Route::controller(ClientController::class)->group(function () {
        Route::get('/clients',  'index');
        Route::post('/clients/store', 'store');
        Route::get('/clients/show/{id}', 'show');
        Route::put('/clients/update/{id}', 'update');
        Route::delete('/clients/delete/{id}', 'destroy');
    });
    Route::controller(ProjetController::class)->group(function () {
        Route::get('/projets',  'index');
        Route::post('/projets/store', 'store');
        Route::get('/projets/show/{id}', 'show');
        Route::put('/projets/update/{id}', 'update');
        Route::delete('/projets/archive/{id}', 'archive');
    });
});
