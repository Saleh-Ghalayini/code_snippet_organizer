<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::group(['prefix' => 'v0.1'], function () {

    // Authenticated Routes
    Route::group(['middleware' => 'auth:api'], function () {

        // User APIs go here
        Route::group(["prefix" => "user"], function () {});

        // Admin APIs go here
        Route::group(["prefix" => "admin", "middleware" => "isAdmin"], function () {});
    });

    Route::group(["prefix" => "guest"], function () {
        Route::post('/login', [AuthController::class, "login"]);
        Route::post('/signup', [AuthController::class, "signup"]);
    });
});
