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
><b>Support open source!</b><br><br>
> This plugin is free but if you use it in a commercial project please consider to sponsor me or make a donation.<br>
> If my work helped you to make some cash it seems fair to me that I might get a little reward as well, right?<br><br>
> Be kind. Share a little. Thanks.<br><br>
> &dash; Bruno<br>
> &nbsp; 

| M | O | N | E | Y |
|---|----|---|---|---|
| [Github sponsor](https://github.com/sponsors/bnomei) | [Patreon](https://patreon.com/bnomei) | [Buy Me a Coffee](https://buymeacoff.ee/bnomei) | [Paypal dontation](https://www.paypal.me/bnomei/15) | [Buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170) |

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-redis-cachedriver/archive/master.zip) as folder `site/plugins/kirby3-redis-cachedriver` or
- `git submodule add https://github.com/bnomei/kirby3-redis-cachedriver.git site/plugins/kirby3-redis-cachedriver` or
- `composer require bnomei/kirby3-redis-cachedriver`

## Why Redis?

[Memcached](https://github.com/memcached/memcached/wiki/ConfiguringServer#commandline-arguments) and [APCu](https://www.php.net/manual/en/apc.configuration.php) have more restrictive defaults. Redis can by very fast with [proper configuration](https://blog.opstree.com/2019/04/16/redis-best-practices-and-performance-tuning/) like `tcp keepalive`.

| Defaults for | Memcached | APCu | Redis |
|----|----|----|----|
| max memory size | 64MB | 32MB | 0 (none) |
| size of key/value pair | 1MB | 4MB | 512MB |

### Setup Content-File Cache

Use [Kirby 3 Boost](https://github.com/bnomei/kirby3-boost) to setup a cache for content files.

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
        'database' => function() { return env('REDIS_DATABASE'); },
        'password' => function() { return env('REDIS_PASSWORD'); },
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

## Settings

| bnomei.redis-cachedriver.            | Default        | Description               |            
|---------------------------|----------------|---------------------------|
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
