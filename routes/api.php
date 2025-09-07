<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\WebsiteController;

Route::prefix('v1')->group(function () {
    Route::apiResource('blogs', BlogController::class);
});

// Website content JSON endpoint: /api/{website}/{lang}/json
Route::get('{website}/{lang}/json', [WebsiteController::class, 'show'])
    ->where(['website' => '[A-Za-z0-9\-_.]+', 'lang' => '[A-Za-z\-_]+' ]);
