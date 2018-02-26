<?php
namespace App\Repository;

use Redis;
use Doctrine\Common\Cache\RedisCache;

class RepositoryHelper
{
    const REDIS_DEFAULT_LIFETIME = 3600 * 24; // one day
    /**
     * @param string $cacheKey
     * @param callable $callback
     *
     * @return array
     */
    public function fetchOrCreate(string $cacheKey, callable $callback): array
    {
        $redis = new Redis();
        $redis->connect('redis_database_server');

        $cacheDriver = new RedisCache();
        $cacheDriver->setRedis($redis);

        if ($cacheDriver->contains($cacheKey)) {
            return $cacheDriver->fetch($cacheKey);
        }

        $results = $callback($cacheDriver);
        $cacheDriver->save($cacheKey, $results, self::REDIS_DEFAULT_LIFETIME);

        return $results;
    }
}