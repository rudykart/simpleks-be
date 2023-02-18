<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BahanBakuController;
use App\Http\Controllers\Api\HitungController;
use App\Http\Controllers\Api\KueController;
use App\Http\Controllers\Api\PerhitunganSimpleksController;
use App\Http\Controllers\Api\PersediaanBahanBakuController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware('auth:sanctum')->get('/user', function (
        Request $request
    ) {
        return $request->user();
    });
    Route::post('logout', [AuthController::class, 'logout']);

    //user
    Route::get('me', [UserController::class, 'index']);

    // Bahan Baku
    Route::get('bahanbaku', [BahanBakuController::class, 'index']);
    Route::post('bahanbaku', [BahanBakuController::class, 'store']);
    Route::middleware(['bahanBaku'])->group(function () {
        Route::get('bahanbaku/{id}', [BahanBakuController::class, 'show']);
        Route::patch('bahanbaku/{id}', [BahanBakuController::class, 'update']);
        Route::delete('bahanbaku/{id}', [
            BahanBakuController::class,
            'destroy',
        ]);
    });

    // Persediaan Bahan Baku
    Route::get('pbb', [PersediaanBahanBakuController::class, 'index']);
    Route::post('pbb', [PersediaanBahanBakuController::class, 'store']);
    Route::middleware(['pbb'])->group(function () {
        Route::get('pbb/{id}', [PersediaanBahanBakuController::class, 'show']);
        Route::patch('pbb/{id}', [
            PersediaanBahanBakuController::class,
            'update',
        ]);
        Route::delete('pbb/{id}', [
            PersediaanBahanBakuController::class,
            'destroy',
        ]);
    });

    // Kue
    Route::get('kue', [KueController::class, 'index']);
    Route::post('kue', [KueController::class, 'store']);
    Route::middleware(['kue'])->group(function () {
        Route::get('kue/{id}', [KueController::class, 'show']);
        Route::patch('kue/{id}', [KueController::class, 'update']);
        Route::delete('kue/{id}', [KueController::class, 'destroy']);
    });

    // Hitung
    Route::get('hitung', [HitungController::class, 'index']);
    Route::post('hitung', [HitungController::class, 'store']);
    Route::middleware(['hitung'])->group(function () {
        Route::get('hitung/{id}', [HitungController::class, 'show']);
        Route::patch('hitung/{id}', [HitungController::class, 'update']);
        Route::delete('hitung/{id}', [HitungController::class, 'destroy']);
    });

    // Perhitungan Simpleks
    Route::post('perhitungansimpleks', [
        PerhitunganSimpleksController::class,
        'store',
    ]);
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
