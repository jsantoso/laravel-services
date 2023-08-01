<?php

use Illuminate\Support\Facades\Route;
use Jsantoso\LaravelServices\SelfTest\Controllers\SelfTestController;

Route::middleware(['api'])->group(function () {
    
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
        Route::get('selfTestResult', [SelfTestController::class, 'getResult']);
    }
});
