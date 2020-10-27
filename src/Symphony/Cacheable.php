<?php

namespace Symphony\Symphony;

/**
 * The Cacheable class provides a wrapper around `Interfaces\CacheInterface`
 * and provides basic CRUD functionality for caching. Historically,
 * this class was hardcoded to use MySQL, but since Symphony 2.4 this
 * may not be the case anymore.
 */
class Cacheable
{
    /**
     * An instance of the iCache class which is where the logic
     * of the cache driver exists.
     *
     * @var iCache
     */
    private $cacheProvider = null;

    /**
     * The constructor for the Cacheable takes an instance of the
     * a class that extends the `iCache` interface. `Symphony::Database()`
     * is accepted a valid `$cacheProvider` to maintain backwards compatibility.
     *
     * @throws InvalidArgumentException
     *
     * @param iCache $cacheProvider
     *                              If a `Symphony::Database()` is provided, the constructor
     *                              will create a `CacheDatabase` interface. If `null`, or the
     *                              `$cacheProvider` is not a class that implements `iCache` or
     *                              `iNamespacedCache` interface an `InvalidArgumentException` will be thrown.
     */
    public function __construct($cacheProvider = null)
    {
        if (($cacheProvider instanceof \MySQL)) {
            $cache = new Cache\CacheDatabase($cacheProvider);
            $this->cacheProvider = $cache;
        } elseif (
            null === $cacheProvider
            || (
                false === $cacheProvider instanceof Interfaces\CacheInterface
                && false === $cacheProvider instanceof Interfaces\NamespacedCacheInterface
            )
        ) {
            throw new \InvalidArgumentException('The cacheProvider must extend the Interfaces\CacheInterface or Interfaces\NamespacedCacheInterface interface.');
        } else {
            $this->cacheProvider = $cacheProvider;
        }
    }

    /**
     * Returns the type of the internal caching provider.
     *
     * @since Symphony 2.4
     *
     * @return string
     */
    public function getType()
    {
        return get_class($this->cacheProvider);
    }

    /**
     * A wrapper for writing data in the cache.
     *
     * @param string $hash
     *                          A
     * @param string $data
     *                          The data to be cached
     * @param int    $ttl
     *                          A integer representing how long the data should be valid for in minutes.
     *                          By default this is null, meaning the data is valid forever
     * @param string $namespace
     *                          Write an item and save in a namespace for ease of bulk operations
     *                          later
     *
     * @return bool
     *              If an error occurs, this function will return false otherwise true
     */
    public function write($hash, $data, $ttl = null, $namespace = null)
    {
        if ($this->cacheProvider instanceof Interfaces\NamespacedCacheInterface) {
            return $this->cacheProvider->write($hash, $data, $ttl, $namespace);
        }

        return $this->cacheProvider->write($hash, $data, $ttl);
    }

    /**
     * Given the hash of a some data, check to see whether it exists the cache.
     *
     * @param string $hash
     *                          The hash of the Cached object, as defined by the user
     * @param string $namespace
     *                          Read multiple items by a namespace
     *
     * @return mixed
     */
    public function read($hash, $namespace = null)
    {
        if ($this->cacheProvider instanceof Interfaces\NamespacedCacheInterface) {
            return $this->cacheProvider->read($hash, $namespace);
        }

        return $this->cacheProvider->read($hash);
    }

    /**
     * Given the hash, this function will remove it from the cache.
     *
     * @param string $hash
     *                          The user defined hash of the data
     * @param string $namespace
     *                          Delete multiple items by a namespace
     *
     * @return bool
     */
    public function delete($hash = null, $namespace = null)
    {
        if ($this->cacheProvider instanceof Interfaces\NamespacedCacheInterface) {
            return $this->cacheProvider->delete($hash, $namespace);
        }

        return $this->cacheProvider->delete($hash);
    }

    /*-------------------------------------------------------------------------
        Utilities:
    -------------------------------------------------------------------------*/

    /**
     * Given some data, this function will compress it using `gzcompress`
     * and then the result is run through `base64_encode` If this fails,
     * false is returned otherwise the compressed data.
     *
     * @param string $data
     *                     The data to compress
     *
     * @return string|bool
     *                     The compressed data, or false if an error occurred
     */
    public static function compressData($data)
    {
        if (!$data = base64_encode(gzcompress($data))) {
            return false;
        }

        return $data;
    }

    /**
     * Given compressed data, this function will decompress it and return
     * the output.
     *
     * @param string $data
     *                     The data to decompress
     *
     * @return string|bool
     *                     The decompressed data, or false if an error occurred
     */
    public static function decompressData($data)
    {
        if (!$data = gzuncompress(base64_decode($data))) {
            return false;
        }

        return $data;
    }
}
