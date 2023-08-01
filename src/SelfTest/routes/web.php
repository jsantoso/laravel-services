<?php

use Illuminate\Support\Facades\Route;
use Jsantoso\LaravelServices\SelfTest\Controllers\SelfTestController;

Route::middleware(['web'])->group(function () {
    
    $config = config('selftest');
    if (
        array_key_exists('enabled', $config) &&
        !$config['enabled']
    ) {
        $enabled = false;
    } else {
        $enabled = true;
    }
    
    if ($enabled) {
        Route::get('selftest', [SelfTestController::class, 'getTestData']);
    }
});
