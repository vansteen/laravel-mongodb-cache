<?php namespace ibraAbuKaff\Mongodb\Cache;

use Illuminate\Cache\CacheManager;

class MongodbCacheManager extends CacheManager
{

    /**
     * Create an instance of the database cache driver.
     *
     * @return \Vansteen\Mongodb\Cache\MongoStore
     */
    protected function createMongodbDriver()
    {
        $connection = $this->getMongodbConnection();
        $encrypter  = $this->app['encrypter'];
        $table      = $this->app['config']['cache.table'];

        $prefix = $this->app['config']['cache.prefix'];

        return $this->repository(new MongodbStore($connection, $encrypter, $table, $prefix));
    }

    /**
     * Get the database connection for the MongoDB driver.
     *
     * @return \Jenssegers\Mongodb\Connection
     *
     * @throws \InvalidArgumentException
     */
    protected function getMongodbConnection()
    {
        $connection = $this->app['config']['cache.connection'];

        // We need to verify if the cache connection is defined
        if (is_null($connection)) {
            throw new \InvalidArgumentException("Cache connection is not defined.");
        } // We need to verify if this cache connection is defined in the database connections
        elseif (!isset($this->app['config']['database.connections'][$connection])) {
            throw new \InvalidArgumentException("Database connection [$connection] is not defined.");
        } // We need to verify if this connection is using the mongodb driver.
        elseif ($this->app['config']['database.connections'][$connection]['driver'] != 'mongodb') {
            throw new \InvalidArgumentException("Database connection [$connection] should use a MongoDB driver.");
        }

        return $this->app['db']->connection($connection);
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['cache.driver'];
    }
}