# Advanced Kirby Redis Cache-Driver

[![Kirby 5](https://flat.badgen.net/badge/Kirby/5?color=ECC748)](https://getkirby.com)
![PHP 8.2](https://flat.badgen.net/badge/PHP/8.2?color=4E5B93&icon=php&label)
![Release](https://flat.badgen.net/packagist/v/bnomei/kirby-redis-cachedriver?color=ae81ff&icon=github&label)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby-redis-cachedriver?color=272822&icon=github&label)
[![Coverage](https://flat.badgen.net/codeclimate/coverage/bnomei/kirby-redis-cachedriver?icon=codeclimate&label)](https://codeclimate.com/github/bnomei/kirby-redis-cachedriver)
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby-redis-cachedriver?icon=codeclimate&label)](https://codeclimate.com/github/bnomei/kirby3-redirects/issues)
[![Discord](https://flat.badgen.net/badge/discord/bnomei?color=7289da&icon=discord&label)](https://discordapp.com/users/bnomei)
[![Buymecoffee](https://flat.badgen.net/badge/icon/donate?icon=buymeacoffee&color=FF813F&label)](https://www.buymeacoffee.com/bnomei)

Advanced Redis cache-driver for Kirby CMS with in-memory store, transactions and preloading

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

> [!TIP]
> From my experience Memcached is very slow compared to APCu or Redis. Just do not use it.

## Setup Cache

Set your Kirby [Cache-Driver](https://getkirby.com/docs/guide/cache#cache-drivers-and-options) to `adredis` for Plugin caches or in your `site/config/config.php`. 
All Redis-related params can be callbacks. You might even load values from an [.env File](https://github.com/bnomei/kirby3-dotenv).

**site/config/config.php**
```php
return [
    'bnomei.turbo.cache.content' => [
        'type' => 'adredis',
        'host' => function() { return env('REDIS_HOST'); },
        'port' => function() { return env('REDIS_PORT'); },
        // 'database' => function() { return env('REDIS_DATABASE'); },
        // 'password' => function() { return env('REDIS_PASSWORD'); },
    ],
];
```

> [!NOTE]
> Why `adredis`? Because Kirby v5 ships with a built-in cache-driver for redis aptly named `redis`. The one from this plugin add in-memory store, transactions and preloading.


### (optional) Setup Cache for Content Files

Combine this plugin with [Kirby Turbo](https://github.com/bnomei/kirby-turbo) to set up a cache for content files.

### How to use Redis Cache Driver with Kirby or other plugins

You must set the cache driver for the [lapse plugin](https://github.com/bnomei/kirby3-lapse) to `redis`.

**site/config/config.php**
```php
<?php
return [
    'otherVendor.pluginName.cache' => ['type' => 'adredis', /*...*/],
    
    // like
    'bnomei.turbo.cache.cmd' => ['type' => 'adredis', /*...*/],
    'bnomei.turbo.cache.model' => ['type' => 'adredis', /*...*/],
    
    // (optional) use a fast cache for Kirby's uuids
    'cache' => [
        'uuid' => ['type' => 'adredis', /*...*/],
    ],
    
    //... other options
];
```

## Usage

### Cache methods
```php
$redis = \Bnomei\Redis::singleton();
$redis->set('key', 'value', $expireInMinutes);
$value = $redis->get('key', $default);
$redis->remove('key');
$redis->flushstore(); // data in memory
$redis->flush(); // memory and prefixed values
$redis->flushdb(); // DANGER: flushes full redis db!!!
```

> [!WARNING]
> When Kirby's global `debug` config is set to `true` no caches will be read and on init the cache will be flushed everytime, but entries will be created.

### Predis Client
```php
$redis = new \Bnomei\Redis($options, $optionsClient);
$client = $redis->redisClient();
$dbsize = $client->dbsize(); // https://bit.ly/2Z8YKyN
```

## Benchmark

```php
$redis = new \Bnomei\Redis($options, $optionsClient);
$redis->benchmark(1000);
```

```shell script
redis : 0.29747581481934
file : 0.24331998825073
```

> [!NOTE]
> The benchmark will create and remove a lot of cache files and entries on redis

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
