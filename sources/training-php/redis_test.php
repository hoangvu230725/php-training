<?php
require_once __DIR__ . '/models/UserModel.php';

// load config redis
$redis = new Redis();
$redis->connect('web-redis', 6379);

// test ghi
$redis->set('framework', 'PHP MVC + Redis');

// test đọc
$value = $redis->get('framework');

echo "Redis test value: " . $value;
