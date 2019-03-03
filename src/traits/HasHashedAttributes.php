<?php

/**
 * HasHashedAttributes.php – laravel-database-key-hashing
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

namespace jacknoordhuis\database\hashing\traits;

trait HasHashedAttributes
{
    /**
     * Determine whether an attribute should be hashed.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function shouldHash($key): bool
    {
        return in_array($key, ((\DatabaseHashing::enabled() and isset($this->hashing)) ? $this->hashing : []));
    }

    /**
     * Set the value of an attribute to a hash of itself.
     *
     * @param string $attribute
     * @param string $salt_modifiers
     *
     * @return bool
     */
    public function hashAttribute(string $attribute, string $salt_modifiers = ""): bool
    {
        if(isset($this->attributes[$attribute])) {
            $this->attributes[$attribute] = \DatabaseHashing::create($this->attributes[$attribute], $salt_modifiers);
            return true;
        }

        return false;
    }

    /**
     * Attempt to hash a stored attribute.
     *
     * @param string $key
     *
     * @return self
     */
    protected function attemptAttributeHash($key): self
    {
        if($this->shouldHash($key)) {
            $this->hashAttribute($this->attributes[$key]);
        }

        return $this;
    }

    //
    // Methods below here override methods within the base Laravel/Illuminate/Eloquent
    // model class and may need adjusting for later releases of Laravel.
    //

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed$value
     */
    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);

        $this->attemptAttributeHash($key);
    }

}