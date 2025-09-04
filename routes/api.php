<?php

use Illuminate\Support\Facades\Route;

// Public content API
Route::get('/{website}/{language}/json', [\App\Http\Controllers\Api\SiteContentController::class, 'show'])
    ->where([
        'website' => '[A-Za-z0-9\-]+' ,
        'language' => '[A-Za-z\-]+' ,
    ]);

// Example authenticated endpoint scaffolded by `install:api`
// Route::get('/user', fn (\Illuminate\Http\Request $request) => $request->user())
//     ->middleware('auth:sanctum');
