# Kirby 3 Redis Cache-Driver

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-redis-cachedriver?color=ae81ff)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-redis-cachedriver?color=272822)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-redis-cachedriver)](https://travis-ci.com/bnomei/kirby3-redis-cachedriver)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-redis-cachedriver)](https://coveralls.io/github/bnomei/kirby3-redis-cachedriver) 
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-redis-cachedriver)](https://codeclimate.com/github/bnomei/kirby3-redis-cachedriver) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Redis based Cache-Driver and Content-File Cache

## Commercial Usage

This plugin is free (MIT license) but if you use it in a commercial project please consider to
- [make a donation 🍻](https://www.paypal.me/bnomei/5) or
- [buy me ☕](https://buymeacoff.ee/bnomei) or
- [buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170)

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-redis-cachedriver/archive/master.zip) as folder `site/plugins/kirby3-redis-cachedriver` or
- `git submodule add https://github.com/bnomei/kirby3-redis-cachedriver.git site/plugins/kirby3-redis-cachedriver` or
- `composer require bnomei/kirby3-redis-cachedriver`

## Why Redis?

At almost same performance [Memcached](https://github.com/memcached/memcached/wiki/ConfiguringServer#commandline-arguments) and [APCu](https://www.php.net/manual/en/apc.configuration.php) have more restrictive defaults. These can be changed but I prefer not having to do so. Both are perfectly fine for storing the compressed html output of most Kirby websites but beyond that consider using Redis.

| Defaults for | Memcached | APCu | Redis |
|----|----|----|----|
| max memory size | 64MB | 32MB | 0 (none) |
| size of key/value pair | 1MB | 4MB | 512MB |

## Setup Pages Cache

Set your Kirby 3 [Cache-Driver](https://getkirby.com/docs/guide/cache#cache-drivers-and-options) to `redis` for all Caches, Plugins or the Kirby Pages Cache in your `site/config/config.php`.

**all caches**
```php
<?php
return [
    'cache' => [
        'type' => 'redis', // default 'file'
    ],
    //... other options
];
```

> KNOWN ISSUE: This does not seem to work (yet).

**per plugin**
```php
<?php
return [
    'bnomei.feed.cache'                     => ['type' => 'redis'],
    'bnomei.fingerprint.cache'              => ['type' => 'redis'],
    'bnomei.handlebars.cache.render'        => ['type' => 'redis'],
    'bnomei.handlebars.cache.files'         => ['type' => 'redis'],
    'bnomei.lapse.cache'                    => ['type' => 'redis'],
    'bnomei.mailjet.cache'                  => ['type' => 'redis'],
    'bnomei.thumbimageoptim.cache.index'    => ['type' => 'redis'],
    'bnomei.thumbimageoptim.cache.stack'    => ['type' => 'redis'],
    //... other options
];
```

**kirby pages**
```php
<?php
return [
    'cache' => [
        // 'type' => 'file', // default
        'pages' => [
            'active' => true,
            'type' => 'redis',
            'prefix' => 'pages',
            'ignore' => function ($page) {
                return $page->id() === 'something';
            }
        ]
    ],
    //... other options
];
```

All redis related params can be callbacks. You might even load values from an [.env File](https://github.com/bnomei/kirby3-dotenv).

**site/config/config.php with callbacks**
 ```php
return [
    'cache' => [
        'pages' => [
            'active' => true,
            'type' => 'redis',
            'host' => function() { return env('REDIS_HOST'); },
            'port' => function() { return env('REDIS_PORT'); },
            'database' => function() { return env('REDIS_DATABASE'); },
            'password' => function() { return env('REDIS_PASSWORD'); },
            'prefix' => 'pages',
            'ignore' => function ($page) {
                return $page->id() === 'something';
            }
        ]
    ],
];
 ```

### Cache methods
```php
$redis = new \Bnomei\Redis($options, $optionsClient);
$redis->set('key', 'value', $expireInMinutes);
$value = $redis->get('key', $default);
$redis->remove('key');
$redis->flush(); // db
```

### Predis Client
```php
$redis = new \Bnomei\Redis($options, $optionsClient);
$client = $redis->redisClient();
$dbsize = $client->dbsize(); // https://bit.ly/2Z8YKyN
```

### Setup Content-File Cache

Use [Kirby 3 Boost](https://github.com/bnomei/kirby3-boost) to setup a cache for content files.

## Settings

| bnomei.redis-cachedriver.            | Default        | Description               |            
|---------------------------|----------------|---------------------------|
| host | `127.0.0.1` |  |
| port | `6379` |  |


## Dependencies

- [nrk/predis](https://github.com/nrk/predis)

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-redis-cachedriver/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
