<?php
// config/backend/redis.php

$redis = new Redis();
$redis->connect(getenv('REDIS_HOST') ?: 'web-redis', getenv('REDIS_PORT') ?: 6379);

$redisPassword = getenv('REDIS_PASSWORD');
if ($redisPassword && $redisPassword !== 'null') {
    $redis->auth($redisPassword);
}

// Trả về đối tượng redis để dùng
return $redis;