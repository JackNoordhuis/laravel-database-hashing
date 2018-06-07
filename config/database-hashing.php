<?php

/**
 * database-hashing.php – laravel-database-key-hashing
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

return [

    /*
    |--------------------------------------------------------------------------
    | Enable database hashing
    |--------------------------------------------------------------------------
    |
    | Enables the database hashing functionality. Defaults to false and reads
    | the 'DB_HASHING_ENABLED' value from the .env file.
    |
    */

    "enabled" => env("DB_HASHING_ENABLED", false),

    /*
    |--------------------------------------------------------------------------
    | Database hash salt
    |--------------------------------------------------------------------------
    |
    | This key is used as the salt for hashes generated, it should be unique in
    | every application as it mitigates the chances of an attacker brute
    | forcing any hashed value. Defaults to the application key but can be
    | a custom value specified by 'DB_HASHING_SALT' in the .env file.
    |
    */

    "salt" => env("DB_HASHING_SALT", env("APP_KEY")),

];