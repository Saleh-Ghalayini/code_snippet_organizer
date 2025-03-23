<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::group(['prefix' => 'v0.1'], function () {

    // Authenticated Routes
    Route::group(['middleware' => 'auth:api'], function () {

        Route::group(["prefix" => "user"], function () {
            // User APIs go here
        });

        Route::group(["prefix" => "admin", "middleware" => "isAdmin"], function () {
            // Admin APIs go here
        });
    });

    Route::group(["prefix" => "guest"], function () {
        Route::post('/login', [AuthController::class, "login"]);
        Route::post('/signup', [AuthController::class, "signup"]);
    });
});
