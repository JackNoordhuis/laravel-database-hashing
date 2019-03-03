<?php

/**
 * HashingServiceProvider.php – laravel-database-key-hashing
 *
 * Copyright (C) 2018 Jack Noordhuis
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Jack
 *
 */

declare(strict_types=1);

namespace jacknoordhuis\database\hashing;

use Illuminate\Support\ServiceProvider;

class HashingServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * This method is called after all other service providers have
     * been registered, meaning you have access to all other services
     * that have been registered by the framework.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([__DIR__ . "/../config/database-hashing.php" => config_path("database-hashing.php")]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . "/../config/database-hashing.php", "database-hashing");

        $this->app->singleton(HashingFacade::getFacadeAccessor(), function($app) {
            return new HashingHelper((bool) config('database-hashing.enabled', false), (string) config('database-hashing.salt', ""));
        });
    }
}