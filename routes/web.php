<?php

use App\Http\Controllers\DataController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('home');
})->name('home');

Route::get('/check/data', function () {
    return Inertia::render('FirePrediction');
})->name('dataform');

Route::post('/check/data', [DataController::class, 'checkData'])->name('checkdata');

Route::get('/check/image', function () {
    return Inertia::render('ImageFirePrediction');
})->name('imageform');

Route::post('/check/image', [DataController::class, 'checkImage'])->name('checkimage');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
