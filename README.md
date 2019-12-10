# Kirby 3 Redis Cache-Driver

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-redis-cachedriver?color=ae81ff)
![Stars](https://flat.badgen.net/packagist/ghs/bnomei/kirby3-redis-cachedriver?color=272822)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-redis-cachedriver?color=272822)
![Issues](https://flat.badgen.net/packagist/ghi/bnomei/kirby3-redis-cachedriver?color=e6db74)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-redis-cachedriver)](https://travis-ci.com/bnomei/kirby3-redis-cachedriver)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-redis-cachedriver)](https://coveralls.io/github/bnomei/kirby3-redis-cachedriver) 
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-redis-cachedriver)](https://codeclimate.com/github/bnomei/kirby3-redis-cachedriver) 
[![Demo](https://flat.badgen.net/badge/website/examples?color=f92672)](https://kirby3-plugins.bnomei.com/redis-cachedriver) 
[![Gitter](https://flat.badgen.net/badge/gitter/chat?color=982ab3)](https://gitter.im/bnomei-kirby-3-plugins/community) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Redis based Cache-Driver

## Commercial Usage

This plugin is free (MIT license) but if you use it in a commercial project please consider to
- [make a donation 🍻](https://www.paypal.me/bnomei/5) or
- [buy me ☕](https://buymeacoff.ee/bnomei) or
- [buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170)

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-redis-cachedriver/archive/master.zip) as folder `site/plugins/kirby3-redis-cachedriver` or
- `git submodule add https://github.com/bnomei/kirby3-redis-cachedriver.git site/plugins/kirby3-redis-cachedriver` or
- `composer require bnomei/kirby3-redis-cachedriver`


## Setup

Set your Kirby 3 [Pages Cache-Driver](https://getkirby.com/docs/guide/cache#cache-drivers-and-options) to `redis`.

**site/config/config.php**

```php
return [
    'cache' => [
        'pages' => [
            'active' => true,
            'type' => 'redis',
            'host' => '127.0.0.1', // default
            'port' => '6379',  // default
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
