<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cache\Cache;
use Kirby\Cache\Value;
use Kirby\Toolkit\A;
use Predis\Client;

final class Redis extends Cache
{
    /**
     * @var array
     */
    private $options;

    /**
     * store for the connection
     * @var Predis\Client
     */
    protected $connection;

    /**
     * Sets all parameters which are needed to connect to Redis
     */
    public function __construct(array $options = [], array $optionsClient = [])
    {
        $this->options = array_merge([
            'host'    => \option('bnomei.redis-cachedriver.host'),
            'port'    => \option('bnomei.redis-cachedriver.port'),
        ], $options);

        foreach ($this->options as $key => $call) {
            if (is_callable($call) && in_array($key, [
                    'host', 'port', 'database', 'password',
                    'persistent', 'prefix', 'read_timeout', 'timeout',
                ])) {
                $this->options[$key] = $call();
            }
        }

        parent::__construct($this->options);
        $this->connection = new Client(
            $options,      // https://github.com/nrk/predis#connecting-to-redis
            $optionsClient // https://github.com/nrk/predis#client-configuration
        );
    }

    public function redisClient(): Client
    {
        return $this->connection;
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function option(?string $key = null)
    {
        if ($key) {
            return A::get($this->options, $key);
        }
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, int $minutes = 0): bool
    {
        $status = $this->connection->set($key, (new Value($value, $minutes))->toJson());
        $this->connection->expire($key, $minutes * 60);

        return $status === 'OK';
    }

    /**
     * @inheritDoc
     */
    public function retrieve(string $key)
    {
        return Value::fromJson($this->connection->get($key));
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        return $this->connection->del($key) > 0;
    }

    /**
     * @inheritDoc
     */
    public function flush(): bool
    {
        return $this->connection->flushdb() === 'OK';
    }
}
