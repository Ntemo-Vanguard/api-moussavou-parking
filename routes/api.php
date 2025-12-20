<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UtilisateurController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\GestionnaireController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\CarteController;
use App\Http\Controllers\API\ParkingController;
use App\Http\Controllers\API\PlaceController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\AccesslogController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PaytechController;
use App\Http\Controllers\API\DashboardController;

Route::middleware('redirectifauthenticatedapi')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});


Route::get('/dashboard/parkings', [DashboardController::class, 'parkingsStatus']);

// Pointage RFID (appelé par Python)
Route::post('/dashboard/access-rfid', [DashboardController::class, 'rfidAccess']);
// Mise à jour des places depuis capteurs 
Route::post('/dashboard/places-update-status', [DashboardController::class, 'updatePlacesStatus']);

// IPN PayTech (PayTech n’a pas de token JWT, donc hors middleware auth:api)
Route::post('/paytech/ipn', [PaytechController::class, 'ipn']);

Route::middleware('auth:api')->group(function () {
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('utilisateurs', UtilisateurController::class);
        Route::apiResource('admins', AdminController::class);
    });
    Route::apiResource('gestionnaires', GestionnaireController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('cartes', CarteController::class);
    Route::post('cartes/{id}/recharger', [CarteController::class, 'recharger']);
    Route::apiResource('parkings', ParkingController::class);
    Route::apiResource('places', PlaceController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('accesslogs', AccesslogController::class);

    // Init paiement depuis l’app Angular (client connecté)
    Route::post('/paytech/init', [PaytechController::class, 'initPayment']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});