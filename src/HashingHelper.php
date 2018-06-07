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
     * Internal version number.
     *
     * @var string
     */
    public const VERSION = "0.0.1";

    /**
     * Specifies if hashing should be enabled.
     *
     * @var bool|null
     */
    private $enabled;

    /**
     * Salt used for generating hashes.
     *
     * @var string
     */
    private $salt;

    public function __construct()
    {
        $this->enabled = config("database-hashing.enabled");
        $this->readSalt();
    }

    /**
     * Get the package version.
     *
     * @throws \Throwable
     *
     * @return string
     */
    public function version(): string
    {
        throw_if(!defined("LARAVEL_DATABASE_HASHING_VERSION"), RuntimeException::class, "The provider did not boot.");

        return LARAVEL_DATABASE_HASHING_VERSION;
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
     * @return string
     */
    public function salt(): string
    {
        return $this->salt;
    }

    /**
     * Generate a hash from the provided data and salt modifier.
     *
     * @param string $value          Data to be hashed.
     * @param string $salt_modifier  A modifier to provide an even more secure hash (eg. a users password).
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
     * Get the singleton of this class.
     *
     * @return HashingHelper
     */
    public static function getInstance(): self
    {
        return HashingFacade::getInstance();
    }

    /**
     * Read the hashing salt from the config and check if it needs decoding.
     *
     * @return void
     */
    private function readSalt(): void
    {
        if($this->enabled) {
            $this->salt = config("database-hashing.salt", null);

            $this->verify($this->salt, "No hashing salt has been specified.");

            // If the key starts with "base64:", we will need to decode the key before handing
            // it off to the encrypter. Keys may be base-64 encoded for presentation and we
            // want to make sure to convert them back to the raw bytes before encrypting.
            if(Str::startsWith($this->salt, "base64:")) {
                $this->salt = base64_decode(substr($this->salt, 7));
            }
        }
    }

    /**
     * Verify a value to ensure it isn't empty or null
     *
     * @param mixed $value
     * @param string $message
     *
     * @return void
     */
    protected function verify($value, string $message): void
    {
        if(empty($value) or is_null($value)) {
            throw new RuntimeException($message);
        }
    }

}