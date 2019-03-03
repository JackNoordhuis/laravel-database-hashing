<?php

/**
 * HashedAttributesTraitTest.php – laravel-database-hashing
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use jacknoordhuis\tests\database\hashing\fixtures\HashingTestModel;
use jacknoordhuis\tests\TestCase;

class HashedAttributesTraitTest extends TestCase
{
    use RefreshDatabase;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
    }

    /**
     * Make sure models with hashed attributes are hashed.
     */
    public function test_create_model_with_hashed_attributes(): void
    {
        $model = HashingTestModel::create([
            'not_hashed' => 'this should not be hashed',
            'is_hashed' => 'this should be hashed',
            'hash_lookup' => 'a unique hashed value',
        ]);

        $this->assertEquals(DatabaseHashing::create('this should be hashed'), $model->is_hashed);
        $this->assertEquals(DatabaseHashing::create('a unique hashed value'), $model->hash_lookup);
    }

    /**
     * Make sure attributes not marked as hashed aren't hashed.
     */
    public function test_create_model_with_not_hashed_attribute(): void
    {
        $model = HashingTestModel::create([
            'not_hashed' => 'this should not be hashed',
            'is_hashed' => 'this should be hashed',
            'hash_lookup' => 'a unique hashed value',
        ]);

        $this->assertEquals('this should not be hashed', $model->not_hashed);
    }

    /**
     * Make sure we can use a hashed attribute as a lookup/index.
     */
    public function test_lookup_by_hash(): void
    {
        $model = HashingTestModel::create([
            'not_hashed' => 'this should not be hashed',
            'is_hashed' => 'this should be hashed',
            'hash_lookup' => 'a unique hashed value',
        ]);
        $lookup = HashingTestModel::where('hash_lookup', $model->hash_lookup)->first();

        $this->assertEquals($model->id, $lookup->id);
    }

    /**
     * Make sure we can use a hashed attribute with a custom salt modifier as a lookup/index.
     */
    public function test_lookup_by_hash_slat_modifier(): void
    {
        $model = HashingTestModel::create([
            'not_hashed' => 'this should not be hashed',
            'is_hashed' => 'this should be hashed',
            'hash_lookup' => 'a unique hashed value',
            'salt_modifier_lookup' => 'a unique hashed value with salt modifier',
        ]);
        $this->assertTrue($model->hashAttribute('salt_modifier_lookup', $model->not_hashed)); //assert that the attribute could be hashed
        $model->save(); //save the model to the database, we updated an attribute

        //recreating the hash later as if the user has logged in and the 'not_hashed' field is their email or username used as a modifier
        $lookup = HashingTestModel::where('salt_modifier_lookup',
            DatabaseHashing::create('a unique hashed value with salt modifier', 'this should not be hashed')
        )->first();

        $this->assertNotNull($lookup);
        $this->assertEquals($model->id, $lookup->id);
    }
}