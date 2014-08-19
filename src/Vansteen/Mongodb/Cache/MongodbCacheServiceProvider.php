<?php namespace Vansteen\Mongodb\Cache;

use Illuminate\Support\ServiceProvider;

class MongodbCacheServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
    $this->app->resolving('cache', function($cache)
    {
        $cache->extend('mongodb', function($app)
        {
            $manager = new MongodbCacheManager($app);

            return $manager->driver('mongodb');
        });
    });
	}
}