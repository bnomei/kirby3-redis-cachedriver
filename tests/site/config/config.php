<?php

return [
    'debug' => false,
    'bnomei.redis-cachedriver.preload' => false, // no pipeline on my test localhost
    'bnomei.redis-cachedriver.key' => function ($key) {
        return str_replace('HELLO', 'WORLD', $key);
    },
];
