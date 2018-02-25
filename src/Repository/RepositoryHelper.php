<?php
namespace App\Repository;

use Redis;
use Doctrine\Common\Cache\RedisCache;

class RepositoryHelper
{
    public function fetchOrCreate(string $cacheKey, $callback): array
    {
        $redis = new Redis();
        $redis->connect('/var/run/redis/redis.sock');

        $cacheDriver = new RedisCache();
        $cacheDriver->setRedis($redis);

        if ($cacheDriver->contains($cacheKey)) {
            return $cacheDriver->fetch($cacheKey);
        }
        return $callback($cacheDriver);
    }
}