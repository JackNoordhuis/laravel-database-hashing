<?php

/**
 * HashingFacadeTest.php – laravel-database-hashing
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

namespace jacknoordhuis\tests\database\hashing;

use DatabaseHashing;
use jacknoordhuis\database\hashing\HashingFacade;
use jacknoordhuis\tests\TestCase;

class HashingFacadeTest extends TestCase
{
    /**
     * Verify the facade accessor/name is what we expect it to be.
     */
    public function test_facade_accessor(): void
    {
        $this->assertEquals(HashingFacade::getFacadeAccessor(), "DatabaseHashing");
    }

    /**
     * Make sure the facade enabled method is enabled, as we expect it to be.
     */
    public function test_facade_enabled(): void
    {
        $this->assertEquals(true, DatabaseHashing::enabled());
    }

    /**
     * Make sure the facade salt method returns the value we expect it to.
     */
    public function test_facade_salt(): void
    {
        $this->assertEquals($this->salt, DatabaseHashing::salt());
    }

    /**
     * Make sure the facade creates the same hash from the same values by default.
     */
    public function test_facade_create(): void
    {
        $this->assertEquals(DatabaseHashing::create('an input'), DatabaseHashing::create('an input'));
    }

    /**
     * Make sure the facade creates a different hash from different values by default.
     */
    public function test_facade_create_different(): void
    {
        $this->assertNotEquals(DatabaseHashing::create('an input'), DatabaseHashing::create('another input'));
    }
}