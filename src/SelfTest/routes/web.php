<?php

use Illuminate\Support\Facades\Route;
use Jsantoso\LaravelServices\SelfTest\Controllers\SelfTestController;

Route::middleware(['web'])->group(function(){   
    Route::get('selftest' , [SelfTestController::class, 'getTestData']);
});
