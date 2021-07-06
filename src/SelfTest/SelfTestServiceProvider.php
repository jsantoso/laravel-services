<?php

namespace Jsantoso\LaravelServices\SelfTest;

use Illuminate\Support\ServiceProvider;

class SelfTestServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $viewPath = __DIR__ . '/resources/views';
        $this->loadViewsFrom($viewPath, 'selftest');

        // Publish a config file
        $configPath = __DIR__ . '/config/selftest.php';
        $this->publishes([$configPath => config_path('selftest.php')], 'config');

        //Include routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/routes/api.php');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $configPath = __DIR__ . '/config/selftest.php';
        $this->mergeConfigFrom($configPath, 'selftest');
    }

}
