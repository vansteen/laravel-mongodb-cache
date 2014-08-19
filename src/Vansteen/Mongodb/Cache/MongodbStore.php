<?php namespace Vansteen\Mongodb\Cache;

use Jenssegers\Mongodb\Connection;
use Illuminate\Cache\StoreInterface;
use Illuminate\Encryption\Encrypter;

class MongodbStore implements StoreInterface {

	/**
	 * The database connection instance.
	 *
	 * @var \Jenssegers\Mongodb\Connection
	 */
	protected $connection;

	/**
	 * The encrypter instance.
	 *
	 * @var \Illuminate\Encryption\Encrypter
	 */
	protected $encrypter;

	/**
	 * The name of the cache collection.
	 *
	 * @var string
	 */
	protected $collection_name;

	/**
	 * A string that should be prepended to keys.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Create a new database store.
	 *
	 * @param  Jenssegers\Mongodb\Connection  $connection
	 * @param  \Illuminate\Encryption\Encrypter  $encrypter
	 * @param  string  $table
	 * @param  string  $prefix
	 * @return void
	 */
	public function __construct(Connection $connection, Encrypter $encrypter, $collection_name, $prefix = '')
	{
		$this->connection = $connection;
		$this->encrypter = $encrypter;
		$this->collection_name = $collection_name;
		$this->prefix = $prefix;
	}

	/**
	 * Retrieve an item from the cache by key.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function get($key)
	{
		$key = $this->prefix . $key;

		$cache = $this->getCacheCollection()->where('key', $key)->first();

		// If we have a cache record we will check the expiration time against current
		// time on the system and see if the record has expired. If it has, we will
		// remove the records from the database collection so it isn't returned again.
		if ( ! is_null($cache))
		{
			if (time() >= $cache['expiration']->sec)
			{
				return $this->forget($key);
			}

			//return $this->encrypter->decrypt($cache['value']);
			return $cache['value'];
		}
	}

	/**
	 * Store an item in the cache for a given number of minutes.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @param  int     $minutes
	 * @return void
	 */
	public function put($key, $value, $minutes)
	{
		$key = $this->prefix . $key;

		// All of the cached values in the database are encrypted in case this is used
		// as a session data store by the consumer. We'll also calculate the expire
		// time and place that on the table so we will check it on our retrieval.
		$value = $this->encrypter->encrypt($value);

		$expiration = new \MongoDate($this->getTime() + ($minutes * 60));

		$data = array('expiration' => $expiration, 'key' => $key, 'value' => $value);

		$item = $this->getCacheCollection()->where('key', $key)->first();

		if(is_null($item))
		{
			$this->getCacheCollection()->insert($data);
		}
		else
		{
			$this->getCacheCollection()->where('key', $key)->update($data);
		}
	}

	/**
	 * Increment the value of an item in the cache.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 *
	 * @throws \LogicException
	 */
	public function increment($key, $value = 1)
	{
		throw new \LogicException("Increment operations not supported by this driver.");
	}

	/**
	 * Increment the value of an item in the cache.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 *
	 * @throws \LogicException
	 */
	public function decrement($key, $value = 1)
	{
		throw new \LogicException("Increment operations not supported by this driver.");
	}

	/**
	 * Get the current system time.
	 *
	 * @return int
	 */
	protected function getTime()
	{
		return time();
	}

	/**
	 * Store an item in the cache indefinitely.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function forever($key, $value)
	{
		return $this->put($key, $value, 5256000);
	}

	/**
	 * Remove an item from the cache.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function forget($key)
	{
		$item = $this->getCacheCollection()->where('key', $key)->first();

		if(!is_null($item))
		{
			$this->getCacheCollection()->where('key', $key)->delete();
		}
	}

	/**
	 * Remove all items from the cache.
	 *
	 * @return void
	 */
	public function flush()
	{
		$mongo_collection = $this->connection->getCollection($this->collection_name);
		$mongo_collection->drop();
	}

	/**
	 * Get the underlying database connection.
	 *
	 * @return \Jenssegers\Mongodb\Connection
	 */
	public function getConnection()
	{
		return $this->connection->getMongoClient();
	}

	/**
	 * Get the encrypter instance.
	 *
	 * @return \Illuminate\Encryption\Encrypter
	 */
	public function getEncrypter()
	{
		return $this->encrypter;
	}

	/**
	 * Get the cache key prefix.
	 *
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * Get the collection.
	 *
	 * @return QueryBuilder
	 */
	protected function getCacheCollection()
	{
		return $this->connection->collection($this->collection_name);
	}
}
