<?php

/**
 * HashingFacade.php – laravel-database-key-hashing
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

use Illuminate\Support\Facades\Facade;
use RuntimeException;

/**
 * @method static bool enabled()
 * @method static string salt()
 * @method static string create(string $value, string $salt_modifier = "")
 */
class HashingFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return "DatabaseHashing";
    }

    /**
     * Get the singleton of HashingHelper.
     *
     * @return HashingHelper
     */
    public static function getInstance()
    {
        return app(self::getFacadeAccessor());
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string $method
     * @param  array  $args
     *
     * @return mixed
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getInstance();
        throw_if(! $instance, RuntimeException::class, 'A facade root has not been set.');
        throw_if(! method_exists($instance, $method), RuntimeException::class, 'Method "'.$method.'" does not exist on "'.get_class($instance).'".');
        return $instance->$method(...$args);
    }

}