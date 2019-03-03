Laravel 5.5+ Database Hashing package
===============
_A package for automatically hashing Eloquent attributes!_

[![Build Status](https://travis-ci.org/JackNoordhuis/laravel-database-hashing.svg?branch=master)](https://travis-ci.org/JackNoordhuis/laravel-database-hashing)

The purpose of this library is to create a set-it-and-forget-it package that can be installed without much effort to hash
eloquent model attributes stored in your database tables.

When enabled, this package will automatically hash the data assigned to attributes that you've specified as they are updated.
This allows you to hide the plain text value of attributes and maintain the ability search the database for
the value (the same input data will always provide the same hash).

All data hashed by this package will have an application specific salt which is specified in the configuration or environment
files, so hashing the same data with a different salt in another application will result in a different output. This adds
layer of complexity/protection against attackers who try to reconstruct your data by attempting to brute force a hash.
If this is not enough, this package also supports providing a secondary salt on top of the application salt, but this cannot
be configured to automatically apply to attributes out of the box.

## Installation

### Step 1: Composer

Via command line:
```bash
$ composer require jacknoordhuis/laravel-database-hashing ^2.0
```
Or add the package to your `composer.json`:
```json
{
    "require": {
        "jacknoordhuis/laravel-database-hashing": "^2.0"
    }
}
```

### Step 2: Enable the package

This package implements Laravel 5.5's package auto-discovery feature. After you install it the package provider and facade
are registered automatically.

If you would like to explicitly declare the provider and/or alias, you can do so by first adding the service provider to
your `config/app.php` file:
```php
'providers' => [
    //
    \jacknoordhuis\database\hashing\HashingServiceProvider::class,
];
```
And then add the alias to your `config/app.php` file:
```php
'aliases' => [
    //
    'DatabaseHashing' => \jacknoordhuis\database\hashing\HashingFacade::class,
];
```

### Step 3: Configure the package

Publish the package config file:
```bash
$ php artisan vendor:publish --provider="jacknoordhuis\database\hashing\HashingServiceProvider"
```
You may now enable automatic hashing of eloquent models by editing the `config/database-hashing.php` file:
```php
return [
    "enabled" => env("DB_HASHING_ENABLED", true),
];
```
Or simply setting the the `DB_HASHING_ENABLED` environment variable to true, via the Laravel `.env` file or hosting environment.
```dotenv
DB_HASHING_ENABLED=true
```

## Usage

Use the `HasHashedAttributes` trait in any eloquent model that you wish to apply automatic attribute hashing to and define
a `protected $hashing` array containing an array of the attributes to automatically hash.

For example:
```php
use jacknoordhuis\database\hashing\traits\HasHashedAttributes;

class User extends Model
{
    use HasHashedAttributes;
   
    /**
     * The attributes that should be hashed when set.
     *
     * @var array
     */
    protected $hashing = [
        'username_lookup',
    ];
}
```

### Looking up hashed values

You can lookup hashed values in your database tables by simply hashing the value you're searching for, as the resulting
hash will always be the same.
```php
$user->username_lookup = $request->get('username'); //the username_lookup attribute will be automatically hashed


//when our user tries to login we just search the database for the hashed value of their username
$user = User::where('username_lookup', DatabaseHashing::create($request->get("username"))->first();
```

You can also optionally provide a salt modifier when hashing data directly, which adds another
level of complexity/security on top of the application-level salt.
```php
//with a salt modifier so we can only ever re-create the hash when the user provides their email or we could store an
//encrypted copy ourselves with another package
$user->username_lookup = $request->get('username'); //set the attribute, then hash manually because we use a modifier
$user->hashAttribute('username_lookup', $request->get('username')); //this time add the plain text email as a salt modifier


//when a user provides their email when logging in, we can replicate the hash and search for the user in the database.
$user = User::where('username_lookup', DatabaseHashing::create($request->get("username"), $request->get("email")))->first();
```

## FAQ's

### Can I manually hash arbitrary data?

Yes! You can manually hash any string using the `DatabaseHashing::create()` global facade.

For example:

```php
    //hash with only application salt
    $hashedEmail = DatabaseHashing::create(Input::get('email'));

    //hash with application salt AND salt modifier
    $hashedEmail = DatabaseHashing::create(Input::get("email"), Input::get('password'));
```

### Can I hash all my model data?

No! The hashing process is irreversible, meaning it should only be used for creating (pseudonymous) identifiers so that
it is still possible to look up data in your database. If you want to *encrypt* your data use a package like
[laravel-database-encryption](https://github.com/austinheap/laravel-database-encryption).

### Should I hash numeric auto-incrementing identifiers?

Probably not. If all data stored in your database is encrypted or hashed then the numeric identifier is effectively anonymous
(it's really pseudonymous) so there is no way to associate any human readable data with the identifier. There are other
reasons for not hashing or encrypting the primary key in your database, and you can read about those [here](https://stackoverflow.com/a/34423898).

### Compatibility with the laravel-database-encryption package

By default these two packages will conflict but we can get around this by implementing our own `setAttribute()` method that
calls both the packages implementations as well:

```php
class User extends Authenticatable
{
    use Notifiable, HasEncryptedAttributes, HasHashedAttributes {
        HasEncryptedAttributes::setAttribute as setEncryptedAttribute;
        HasHashedAttributes::setAttribute as setHashedAttribute;
    }

    protected $fillable = [
        'name', 'email', 'email_lookup', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $encrypted = [
        'name', 'email',
    ];

    protected $hashing = [
        'email_lookup',
    ];

    /**
     * Overwrite the method so we can attempt to encrypt OR hash an
     * attribute without the traits colliding.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute($key, $value)
    {
        $this->setEncryptedAttribute($key, $value); //attempt to encrypt the attribute

        $current = $this->attributes[$key] ?? null; //fetch the current value of the attribute
        if($current === $value) { //check to make sure the attribute wasn't modified (we will never hash an encrypted attribute)
            $this->setHashedAttribute($key, $value); //attempt to hash the attribute
        }
    }
}
```

This can be extracted into it's own trait if it is needed across multiple models in your project. This same approach can
also be used to make any package that implements the `setAttribute()` method on models compatible.