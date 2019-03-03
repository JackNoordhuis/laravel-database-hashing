<?php

/**
 * HashingHelperTest.php – laravel-database-hashing
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

use jacknoordhuis\database\hashing\HashingHelper;
use jacknoordhuis\tests\TestCase;
use function base64_encode;

class HashingHelperTest extends TestCase
{
    /**
     * Make sure the salt is loaded correctly when the hashing functionality is enabled.
     */
    public function test_enabled_helper(): void
    {
        $helper = new HashingHelper(true, 'this is a salt');

        $this->assertTrue($helper->enabled());
        $this->assertEquals('this is a salt', $helper->salt());
    }

    /**
     * Make sure a base64 encoded salt is loaded correctly.
     */
    public function test_enabled_base64(): void
    {
        $helper = new HashingHelper(true, 'base64:' . base64_encode('this is a base64 salt'));

        $this->assertTrue($helper->enabled());
        $this->assertEquals('this is a base64 salt', $helper->salt());
    }

    /**
     * Make sure the salt isn't loaded if the hashing functionality is disabled.
     */
    public function test_disabled_helper(): void
    {
        $helper = new HashingHelper(false, '');

        $this->assertFalse($helper->enabled());
        $this->assertNull($helper->salt());
    }

    /**
     * Make sure the same application salt and input value create the same hash.
     */
    public function test_same_app_salt_hash(): void
    {
        $helper = new HashingHelper(true, 'an application salt');
        $hash_1 = $helper->create('some random data');
        $hash_2 = $helper->create('some random data');

        $this->assertEquals($hash_1, $hash_2);
    }

    /**
     * Make sure two different application salts create different hashes on the same data.
     */
    public function test_different_app_salt_hash(): void
    {
        $hash_1 = (new HashingHelper(true, 'an application salt'))->create('some random data');
        $hash_2 = (new HashingHelper(true, 'another application salt'))->create('some random data');

        $this->assertNotEquals($hash_1, $hash_2);
    }

    /**
     * Make sure the same application salt creates a different hash for each input value.
     */
    public function test_different_value(): void
    {
        $helper = new HashingHelper(true, 'an application salt');
        $hash_1 = $helper->create('some random data');
        $hash_2 = $helper->create('some more random data');

        $this->assertNotEquals($hash_1, $hash_2);
    }

    /**
     * Make sure the same application salt, input values and salt modifiers create the same hash.
     */
    public function test_same_salt_modifier(): void
    {
        $helper = new HashingHelper(true, 'an application salt');
        $hash_1 = $helper->create('some random data', 'a custom salt modifier');
        $hash_2 = $helper->create('some random data', 'a custom salt modifier');

        $this->assertEquals($hash_1, $hash_2);
    }

    /**
     * Make sure the same application salt and input values with different salt modifiers creates different hashes.
     */
    public function test_different_salt_modifier(): void
    {
        $helper = new HashingHelper(true, 'an application salt');
        $hash_1 = $helper->create('some random data', 'a custom salt modifier');
        $hash_2 = $helper->create('some random data', 'another custom salt modifier');

        $this->assertNotEquals($hash_1, $hash_2);
    }
}