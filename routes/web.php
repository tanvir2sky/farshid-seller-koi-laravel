<?php

use Illuminate\Support\Facades\Route;

Route::view('/offline', 'pwa.offline')->name('pwa.offline');

Route::get('/manifest.webmanifest', function () {
    return response()
        ->view('pwa.manifest')
        ->header('Content-Type', 'application/manifest+json; charset=utf-8');
})->name('pwa.manifest');

Route::get('/sw.js', function () {
    return response()
        ->view('pwa.service-worker')
        ->header('Content-Type', 'application/javascript; charset=utf-8')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Service-Worker-Allowed', '/');
})->name('pwa.sw');
