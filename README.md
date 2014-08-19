Laravel MongoDB Cache
=====================

*Currently in dev but this version seems fullly working. I need to do a last check and write few tests.*

A MongoDB cache driver for Laravel 4 and the package [jenssegers\mongodb](https://github.com/jenssegers/Laravel-MongoDB).

For more information about Caches, check http://laravel.com/docs/cache.

Installation
------------

Make sure you have [jenssegers\mongodb](https://github.com/jenssegers/Laravel-MongoDB) installed and configured before you continue.

Add the package to your `composer.json` and run `composer update`.

```php
{
  "require": {
    "vansteen/laravel-mongodb-cache": "dev-master"
  }
}
```

Add the cache service provider in `app/config/app.php`:

```php
    'Vansteen\Mongodb\Cache\MongodbCacheServiceProvider',
```

Change the cache driver in `app/config/cache.php` to mongodb:

```php
  'driver' => 'mongodb',
```

Change the cache connection to a database connection using the mongodb driver from `app/config/database.php`:

```php
  'connection' => 'my_mongodb_connection',
```
