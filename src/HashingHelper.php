<?php

/**
 * HashingHelper.php – laravel-database-key-hashing
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

use Illuminate\Support\Str;
use RuntimeException;

class HashingHelper
{
    /**
     * Specifies if hashing should be enabled.
     *
     * @var bool|null
     */
    private $enabled;

    /**
     * Salt used for generating hashes.
     *
     * @var string|null
     */
    private $salt = null;

    /**
     * @param bool $enabled
     * @param string $app_salt
     */
    public function __construct(bool $enabled, string $app_salt)
    {
        $this->enabled = $enabled;

        if ($enabled) {
            $this->setUpSalt($app_salt);
        }
    }

    /**
     * Check if the hashing functionality is enabled.
     *
     * @return bool
     */
    public function enabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Retrieve the salt used for hashing.
     *
     * @return string|null
     */
    public function salt(): ?string
    {
        return $this->salt;
    }

    /**
     * Generate a hash from the provided data and salt modifier.
     *
     * @param string $value Data to be hashed.
     * @param string $salt_modifier A modifier to provide an even more secure hash (eg. a users password).
     *
     *
     * NOTE: Any data passed to $salt_modifier must be provided in order to reproduce the hash. Only use it in situations where
     * you already have the modifier (eg. when attempting to login via password) otherwise the hash will be different and you
     * won't be able to search a database for matches.
     *
     *
     * @return string
     */
    public function create(string $value, string $salt_modifier = ""): string
    {
        return bin2hex(hash("sha512", $value . $this->salt . $salt_modifier, true) ^ hash("whirlpool", $salt_modifier . $this->salt . $value, true));
    }

    /**
     * Check the application salt is valid and decode from base64 if needed.
     *
     * @param string $salt
     */
    private function setUpSalt(string $salt): void
    {
        throw_if(strlen($salt === 0 or $salt === null), RuntimeException::class, 'No hashing salt has been specified.');

        // If the salt starts with "base64:", we will need to decode it before using it
        // to hash anything. A salt may be base64 encoded for presentation and we want
        // it converted back into raw bytes before using it in our hashing algo.
        if (Str::startsWith($salt, "base64:")) {
            $salt = base64_decode(substr($salt, 7));
        }

        $this->salt = $salt;
    }
}