<?php

@include_once __DIR__ . '/vendor/autoload.php';

if (!class_exists('Bnomei\Redis')) {
    require_once __DIR__ . '/classes/Redis.php';
}
if (!class_exists('Bnomei\RedisPage')) {
    require_once __DIR__ . '/classes/RedisPage.php';
}

Kirby::plugin('bnomei/redis-cachedriver', [
    'options' => [
        'host'    => '127.0.0.1',
        'port'    => 6379,
    ],
    'cacheTypes' => [
        'redis' => \Bnomei\Redis::class
    ]
]);
