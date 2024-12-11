<?php

require_once __DIR__.'/../vendor/autoload.php';

use Bnomei\Redis;

$redis = null;
function redis(bool $force = false): Redis
{
    global $redis;
    if ($redis && $force === false) {
        return $redis;
    }
    $redis = new Redis([
        'prefix' => 'unittest',
        'host' => function () {
            return 'localhost';
            //return $_ENV['REDIS_HOST'];
        },
        'port' => function () {
            return 6379;
            //return intval($_ENV['REDIS_PORT']);
        },
    ]);

    return $redis;
}

beforeEach(function () {
    redis()->flushdb(); // ALL DATA in Redis DB
});

test('construct', function () {
    expect(redis())->toBeInstanceOf(Redis::class);
});

test('client', function () {
    expect(redis()->redisClient())->toBeInstanceOf(Predis\Client::class);
});

test('flush', function () {
    redis()->flushdb();
    expect(redis()->get('something'))->toBeNull();
});

test('api', function () {
    // flush and none
    redis()->flushdb();
    expect(redis()->get('something'))->toBeNull();

    // default
    expect(redis()->get('something', 'weird'))->toEqual('weird');

    // set and get infinite
    expect(redis()->set('something', 'wicked'))->toBeTrue()
        ->and(redis()->get('something'))->toEqual('wicked');

    // remove
    redis()->remove('something');
    expect(redis()->get('something'))->toBeNull();

    // with expiration
    redis()->set('something', 'wobbly', 1);
    expect(redis()->get('something'))->toEqual('wobbly');
});

test('transaction', function () {
    redis()->flushdb();

    redis()->beginTransaction();
    for ($i = 0; $i <= 5; $i++) {
        redis()->set('something'.$i, 'wicked'.$i);
    }
    redis()->endTransaction();

    expect(redis()->get('something5'))->toEqual('wicked5');

    // flush data in store forcing a read from db
    redis()->flushstore();
    // flush not flushdb
    expect(redis()->get('something5'))->toEqual('wicked5');
});

test('preload', function () {
    redis()->flushdb();

    redis()->beginTransaction();
    for ($i = 0; $i <= 50; $i++) {
        redis()->set('something'.$i, 'wicked'.$i);
    }
    redis()->endTransaction();

    redis()->flushstore();
    // flush not flushdb
    expect(count(redis()->preloadList()))->toEqual(0);

    // request a few and see if preload matches
    expect(redis()->get('something14'))->toEqual('wicked14')
        ->and(redis()->get('something42'))->toEqual('wicked42');

    // last one twice should only create one record
    expect(redis()->get('something5'))->toEqual('wicked5')
        ->and(redis()->get('something5'))->toEqual('wicked5')
        ->and(count(redis()->preloadList()))->toEqual(3);
});

test('benchmark', function () {
    redis()->flush();
    redis()->benchmark(1000);
    global $redis;
    unset($redis);
    // will happen at end of pageview
    expect(true)->toBeTrue();
});

test('replace in key', function () {
    // see site/config/config.php
    redis()->remove('HELLO');
    redis()->remove('WORLD');
    redis()->set('HELLO', 'world');
    expect(redis()->get('WORLD'))->toEqual('world');
});
