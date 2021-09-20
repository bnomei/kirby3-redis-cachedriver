<?php

@include_once __DIR__ . '/vendor/autoload.php';

if (!class_exists('Bnomei\Redis')) {
    require_once __DIR__ . '/classes/Redis.php';
}

Kirby::plugin('bnomei/redis-cachedriver', [
    'options' => [
        // plugin
        'cache'   => true,
        'preload' => true, // or minutes

        // redis
        'host'    => '127.0.0.1',
        'port'    => 6379,
    ],
    'cacheTypes' => [
        'redis' => \Bnomei\Redis::class
    ]
]);
