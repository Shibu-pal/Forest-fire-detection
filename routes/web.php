<?php

use App\Http\Controllers\DataController;
use App\Http\Controllers\IVRController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Response;

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

Route::match(['get', 'post'], '/voice', [IVRController::class, 'showLanguageMenu'])->name('ivr.welcome')->withoutMiddleware([VerifyCsrfToken::class]);
Route::match(['get', 'post'], '/menu', [IVRController::class, 'showMenuResponse'])->name('ivr.menu')->withoutMiddleware([VerifyCsrfToken::class]);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
