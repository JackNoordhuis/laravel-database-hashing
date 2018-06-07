Laravel 5.5+ Database Hashing package
===============
_A package for automatically hashing Eloquent attributes!_

The purpose of this project is to create a set-it-and-forget-it package that can be installed without much effort to hash Eloquent model attributes stored in your database tables.

When enabled, it automagically begins hashing data as it is stored in the model attributes so that you can still perform lookups for data in your database.

All data is hashed with an application specific salt which is specified in the configuration or environment files so hashing the same data with a different salt in another application will result in a different output, this adds another layer of complexity for attackers who try to brute force your data.

##Installation

###Step 1: Composer

Via command line:
```bash
$ composer require jacknoordhuis/laravel-database-hashing
```
Or add the package to your `composer.json`:
```json
{
    "require": {
        "jacknoordhuis/laravel-database-hashing": "*"
    }
}
```

###Step 2: Enable the package

This package implements Laravel 5.5's auto-discovery feature. After you install it the package provider and facade are added automatically.

If you would like to declare the provider and/or alias explicitly, you may do so by first adding the service provider to your `config/app.php` file:
```php
'providers' => [
    //
    jacknoordhuis\database\hashing\HashingServiceProvider::class,
];
```
And then add the alias to your `config/app.php` file:
```php
'aliases' => [
    //
    'DatabaseHashing' => jacknoordhuis\database\hashing\HashingFacade::class,
];
```
###Step 3: Configure the package

Publish the package config file:
```bash
$ php artisan vendor:publish --provider="jacknoordhuis\database\hashing\HashingServiceProvider"
```
You may now enable automagic hashing of Eloquent models by editing the `config/database-hashing.php` file:
```php
return [
    "enabled" => env("DB_HASHING_ENABLED", true),
];
```
Or simply setting the the `DB_HASHING_ENABLED` environment variable to true, via the Laravel `.env` file or hosting environment.
```dotenv
DB_HASHING_ENABLED=true
```

##Usage

Use the `HasHashedAttributes` trait in any Eloquent model that you wish to apply hashing to and define a `protected $hashing` array containing a list of the attributes to hash.

For example:
```php
    use jacknoordhuis\database\hashing\traits\HasHashedAttributes;

    class User extends Eloquent {
        use HasHashedAttributes;
       
        /**
         * The attributes that should be hashed on save.
         *
         * @var array
         */
        protected $hashing = [
            "username_lookup",
        ];
    }
```

###Looking up hashed values
You can lookup hashed values in your database tables by simply hashing the value you're searching for as the resulting hash will be the same. You can also optionally provide a salt modifier when hashing data directly, which adds another level of complexity on top of the application-level salt.
```php
// Assign a hashed value of the username to a users username_lookup attribute with the password
// as a salt modifier so we can only ever re-create the hash when the user provides their password.
$user->username_lookup = $user->hashAttribute($username, $password);


// And when a user provides their password when logging in we can replicate the hash and search for
// the user in the database.
User::where("username_lookup", "=", \DatabaseHashing::create($request->get("username"), $request->get("password")));
```

##FAQ's

###Can I manually hash arbitrary data?

Yes! You can manually encrypt or decrypt data using the functions `hashAttribute()` on models and `\DatabaseHashing::create()` globally. For example:

```php
    // With models using the HasHashedAttributes trait:
    $user = new User();
    $hashedEmail = $user->hashAttribute(Input::get("email"));

    // or

    // Globally using the DatabaseHasing facade:
    $hashedEmail = \DatabaseHashing::create(Input::get("email"));
```

###Can I hash all my model data?

No! The hashing process is irreversible, meaning it should only be used for creating (pseudonymous) identifiers so that it's still possible to look up data in your database. If you want to *encrypt* your data use a package like [this](https://github.com/austinheap/laravel-database-encryption).

###Should I hash numeric auto-incrementing identifiers?

Probably not. If all data stored in your database is encrypted or hashed then the numeric identifier is effectively anonymous (it's really pseudonymous) so there is no way the associate any human readable data with the identifier. There are are other reasons for not hashing or encrypting the primary key and you can read about those [here](https://stackoverflow.com/a/34423898).