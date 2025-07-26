<?php

use App\Http\Controllers\DataController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('home');
})->name('home');

Route::post('/check', [DataController::class, 'checkData'])->name('check');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
