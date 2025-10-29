<?php

use App\Http\Controllers\DataController;
use App\Http\Controllers\IVRController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

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

Route::get('/_debug/log', function () {
    return response()->file(storage_path('logs/laravel.log'));
});
Route::get('/_debug/files', function() {
    return response()->json([
        'tmp_files' => Storage::files('tmp'),
        'log_exists' => file_exists(storage_path('logs/laravel.log')),
    ]);
});
Route::get('/show-tmp/{filename}', function ($filename) {
    $path = storage_path('app/private/tmp/' . $filename);
    
    if (!File::exists($path)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    $content = File::get($path);
    $mime = File::mimeType($path);

    return Response::make($content, 200, [
        'Content-Type' => $mime,
    ]);
});
Route::get('/_env_debug', function() {
    if (request()->query('key') !== env('TMP_VIEW_KEY', 'secret-debug-key')) {
        abort(403);
    }
    return response()->json([
        'env_TWILIO_SID' => env('TWILIO_SID'),
        'env_TWILIO_TOKEN' => env('TWILIO_AUTH_TOKEN'),
        'getenv_TWILIO_SID' => getenv('TWILIO_SID'),
        '_ENV_keys' => array_intersect(array_keys($_ENV), ['TWILIO_SID','TWILIO_AUTH_TOKEN']),
        '_SERVER_keys' => array_intersect(array_keys($_SERVER), ['TWILIO_SID','TWILIO_AUTH_TOKEN']),
        'app_url' => env('APP_URL'),
    ]);
});
Route::get('/clear-config', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    return 'Config cleared and rebuilt!';
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
