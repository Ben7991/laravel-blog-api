<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post("users/register", [UsersController::class, "register"]);
Route::post("users/login", [UsersController::class, "login"]);
Route::get("users/username", [UsersController::class, "checkUsername"]);

Route::middleware(["auth:sanctum", "user.admin"])->group(function() {
    Route::get("users", [UsersController::class, "index"]);

    Route::resource("categories", CategoriesController::class)->only([
        "index", "store", "update",
    ]);

    Route::resource("tags", TagsController::class)->only([
        "index", "store", "update",
    ]);

    Route::resource("posts", PostsController::class)->only([
        "index", "store", "update"
    ]);
});
