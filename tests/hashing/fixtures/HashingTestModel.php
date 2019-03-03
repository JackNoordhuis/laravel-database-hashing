<?php

/**
 * HashingTestModel.php – laravel-database-hashing
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

namespace jacknoordhuis\tests\database\hashing\fixtures;

use Illuminate\Database\Eloquent\Model;
use jacknoordhuis\database\hashing\traits\HasHashedAttributes;

class HashingTestModel extends Model
{
    use HasHashedAttributes;

    protected $table = 'test_model';

    protected $fillable = [
        'not_hashed',
        'is_hashed',
        'hash_lookup',
        'salt_modifier_lookup' //we will hash this ourselves with a salt modifier
    ];

    protected $hashing = [
        'is_hashed',
        'hash_lookup'
    ];
}