<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Provide a default login route name for packages that expect it.
// Redirects to Filament's admin login page.
Route::redirect('/login', '/admin/login')->name('login');
