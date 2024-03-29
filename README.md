# Kirby 3 Redis Cache-Driver

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-redis-cachedriver?color=ae81ff)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-redis-cachedriver?color=272822)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-redis-cachedriver)](https://travis-ci.com/bnomei/kirby3-redis-cachedriver)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-redis-cachedriver)](https://coveralls.io/github/bnomei/kirby3-redis-cachedriver) 
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-redis-cachedriver)](https://codeclimate.com/github/bnomei/kirby3-redis-cachedriver) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Redis based Cache-Driver

## Commerical Usage

> <br>
> <b>Support open source!</b><br><br>
> This plugin is free but if you use it in a commercial project please consider to sponsor me or make a donation.<br>
> If my work helped you to make some cash it seems fair to me that I might get a little reward as well, right?<br><br>
> Be kind. Share a little. Thanks.<br><br>
> &dash; Bruno<br>
> &nbsp; 

| M | O | N | E | Y |
|---|----|---|---|---|
| [Github sponsor](https://github.com/sponsors/bnomei) | [Patreon](https://patreon.com/bnomei) | [Buy Me a Coffee](https://buymeacoff.ee/bnomei) | [Paypal dontation](https://www.paypal.me/bnomei/15) | [Hire me](mailto:b@bnomei.com?subject=Kirby) |

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-redis-cachedriver/archive/master.zip) as folder `site/plugins/kirby3-redis-cachedriver` or
- `git submodule add https://github.com/bnomei/kirby3-redis-cachedriver.git site/plugins/kirby3-redis-cachedriver` or
- `composer require bnomei/kirby3-redis-cachedriver`

## Why Redis?

[Memcached](https://github.com/memcached/memcached/wiki/ConfiguringServer#commandline-arguments) and [APCu](https://www.php.net/manual/en/apc.configuration.php) have more restrictive defaults. Redis does not have these limitations and is by very fast with [proper configuration](https://blog.opstree.com/2019/04/16/redis-best-practices-and-performance-tuning/).

| Defaults for | Memcached | APCu | Redis |
|----|----|----|----|
| max memory size | 64MB | 32MB | 0 (none) |
| size of key/value pair | 1MB | 4MB | 512MB |

## Setup Cache

Set your Kirby 3 [Cache-Driver](https://getkirby.com/docs/guide/cache#cache-drivers-and-options) to `redis` for Plugin caches or in your `site/config/config.php`. 
All redis related params can be callbacks. You might even load values from an [.env File](https://github.com/bnomei/kirby3-dotenv).

**site/config/config.php**
 ```php
return [
    'bnomei.boost.cache' => [
        'type' => 'redis',
        'host' => function() { return env('REDIS_HOST'); },
        'port' => function() { return env('REDIS_PORT'); },
        // 'database' => function() { return env('REDIS_DATABASE'); },
        // 'password' => function() { return env('REDIS_PASSWORD'); },
    ],
];
 ```

### Cache methods
```php
$redis = \Bnomei\Redis::singleton();
$redis->set('key', 'value', $expireInMinutes);
$value = $redis->get('key', $default);
$redis->remove('key');
$redis->flush(); // data in memory
$redis->flushdb(); // DANGER: flushes full redis db!!!
```

### Predis Client
```php
$redis = new \Bnomei\Redis($options, $optionsClient);
$client = $redis->redisClient();
$dbsize = $client->dbsize(); // https://bit.ly/2Z8YKyN
```

### Benchmark

```php
$redis = new \Bnomei\Redis($options, $optionsClient);
$redis->benchmark(1000);
```

```shell script
redis : 0.29747581481934
file : 0.24331998825073
```

> ATTENTION: This will create and remove a lot of cache files and entries on redis

### No cache when debugging

When Kirbys global debug config is set to `true` no caches will be read. But entries will be created. This will make you live easier – trust me.

### How to use Redis Cache Driver with Lapse or Boost

You need to set the cache driver for the [lapse plugin](https://github.com/bnomei/kirby3-lapse) to `redis`.

**site/config/config.php**
```php
<?php
return [
    'bnomei.lapse.cache' => ['type' => 'redis'],
    'bnomei.boost.cache' => ['type' => 'redis'],
    //... other options
];
```

### Setup Content-File Cache

Use [Kirby 3 Boost](https://github.com/bnomei/kirby3-boost) to setup a cache for content files.


## Settings

| bnomei.redis-cachedriver.            | Default        | Description               |            
|---------------------------|----------------|---------------------------|
| store | `true` | keep accessed cache items stored in PHP memory for faster recurring access  |
| store-ignore | `` | if key contains that string then ignore  |
| preload | `true` | bool|int in minutes, will cache preload recently used data using a pipeline on init  |
| host | `127.0.0.1` |  |
| port | `6379` |  |


## Dependencies

- [nrk/predis](https://github.com/nrk/predis)

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-redis-cachedriver/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
