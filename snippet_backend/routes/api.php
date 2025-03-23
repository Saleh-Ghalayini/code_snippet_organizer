<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SnippetController;

Route::group(["prefix" => "v0.1"], function () {

    // Authenticated Routes
    Route::group(["middleware" => "auth:api"], function () {

        // User APIs go here
        Route::group(["prefix" => "user"], function () {
            Route::get('/tags', [SnippetController::class, 'getTags'])->name('snippets.getTag');
            Route::post("/snippets", [SnippetController::class, "addSnippet"])->name('snippets.add');
            Route::get("/snippets", [SnippetController::class, "displayAll"])->name('snippets.displayAll');
            Route::put("/snippets/{id}", [SnippetController::class, "updateSnippet"])->name('snippets.update');
            Route::get("/snippets/search", [SnippetController::class, "searchSnippet"])->name('snippets.search');
            Route::delete("/snippets/{id}", [SnippetController::class, "deleteSnippet"])->name('snippets.delete');
            Route::post("/snippets/restore/{id}", [SnippetController::class, "restoreSnippet"])->name('snippets.restore');
            Route::get('/snippets/favourites', [SnippetController::class, 'displayFavourites'])->name('snippets.displayFavourite');
            Route::post("/snippets/favourite/{id}", [SnippetController::class, "toggleFavourite"])->name('snippets.toggleFavourite');
            Route::delete("/snippets/permanent-delete/{id}", [SnippetController::class, "permanentDeleteSnippet"])->name('snippets.permanentDelete');
        });

        // Admin APIs go here
        //Route::group(["prefix" => "admin", "middleware" => "isAdmin"], function () {});
    });

    Route::group(["prefix" => "guest"], function () {
        Route::post("/login", [AuthController::class, "login"])->name('login');
        Route::post("/signup", [AuthController::class, "signup"])->name('signup');
    });
});
