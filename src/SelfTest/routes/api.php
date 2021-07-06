<?php

use Illuminate\Support\Facades\Route;
use Jsantoso\LaravelServices\SelfTest\Controllers\SelfTestController;

Route::middleware(['api'])->group(function(){   
    Route::get('selfTestResult' , [SelfTestController::class, 'getResult']);
});
