<?php

/**
 * TestCase.php – laravel-database-hashing
 *
 * Copyright (C) 2019 Jack Noordhuis
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

namespace jacknoordhuis\tests;

use jacknoordhuis\database\hashing\HashingFacade;
use jacknoordhuis\database\hashing\HashingServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getEnvironmentSetUp($app) : void
    {
        $app['config']->set('database-hashing.enabled', true);
        $app['config']->set('database-hashing.salt', str_random(32));
    }

    protected function getPackageProviders($app) : array
    {
        return [
            HashingServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app) : array
    {
        return [
            'DatabaseHashing' => HashingFacade::class,
        ];
    }
}