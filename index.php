<?php

@include_once __DIR__ . '/vendor/autoload.php';

if (!class_exists('Bnomei\Redis')) {
    require_once __DIR__ . '/classes/Redis.php';
}

Kirby::plugin('bnomei/redis-cachedriver', [
    'options' => [
        // plugin
        'cache'   => true,
        'store' => true, // php memory cache
        'store-ignore' => '', // if key contains then ignore
        'preload' => true, // or minutes
        'key' => function($key) { return $key; },

        // redis
        'host'    => '127.0.0.1',
        'port'    => 6379,
    ],
    'cacheTypes' => [
        'redis' => \Bnomei\Redis::class
    ]
]);
