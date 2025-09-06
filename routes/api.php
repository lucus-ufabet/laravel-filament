<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BlogController;

Route::prefix('v1')->group(function () {
    Route::apiResource('blogs', BlogController::class);
});

