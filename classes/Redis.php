<?php

declare(strict_types=1);

namespace Bnomei;

use Closure;
use Kirby\Cache\Cache;
use Kirby\Cache\FileCache;
use Kirby\Cache\Value;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Predis\Client;
use Predis\Response\Status;

final class Redis extends Cache
{
    protected array $options = [];

    private array $shutdownCallbacks = [];

    protected Client $connection;

    private array $preload;

    private array $store;

    private mixed $transaction;

    private int $transactionsCount = 0;

    /**
     * Sets all parameters which are needed to connect to Redis
     */
    public function __construct(array $options = [], array $optionsClient = [])
    {
        $this->options = array_merge([
            'debug' => \option('debug'),
            'store' => \option('bnomei.redis-cachedriver.store'),
            'flush-on-debug' => \option('bnomei.redis-cachedriver.flush-on-debug'),
            'store-ignore' => \option('bnomei.redis-cachedriver.store-ignore'),
            'preload' => \option('bnomei.redis-cachedriver.preload'),
            'key' => \option('bnomei.redis-cachedriver.key'),
            'host' => \option('bnomei.redis-cachedriver.host'),
            'port' => \option('bnomei.redis-cachedriver.port'),
            'transaction_limit' => intval(\option('bnomei.transaction.limit')),
        ], $options);

        foreach ($this->options as $key => $call) {
            if ($call instanceof Closure && in_array($key, [
                'host', 'port', 'database', 'password',
                'persistent', 'prefix', 'read_timeout', 'timeout',
            ])) {
                $this->options[$key] = $call();
            }
        }

        parent::__construct($this->options);

        $this->connection = new Client(
            $this->options, // https://github.com/nrk/predis#connecting-to-redis
            $optionsClient // https://github.com/nrk/predis#client-configuration
        );
        $this->transaction = null;

        if ($this->options['debug'] && $this->options['flush-on-debug']) {
            $this->flush();
        }

        $this->store = [];
        $this->preload();
    }

    public function register_shutdown_function(Closure $callback): void
    {
        $this->shutdownCallbacks[] = $callback;
    }

    public function __destruct()
    {
        foreach ($this->shutdownCallbacks as $callback) {
            if (! is_string($callback) && is_callable($callback)) {
                $callback();
            }
        }

        if ($this->options['debug']) {
            return;
        }

        if ($this->options['preload'] !== false) {
            kirby()->cache('bnomei.redis-cachedriver')->set('preload', $this->preload, 0);
        }
    }

    public function redisClient(): Client
    {
        return $this->connection;
    }

    private function preload(): void
    {
        $this->preload = [];
        $expire = $this->options['preload'];
        if ($expire === false) {
            return;
        } elseif (is_int($expire)) {
            $expire = time() - $expire * 60;
        }

        $this->preload = kirby()->cache('bnomei.redis-cachedriver')->get('preload', []);
        if (count($this->preload) === 0) {
            return;
        }

        $garbage = [];
        foreach ($this->preload as $key => $timestamp) {
            if ($timestamp < $expire) {
                $garbage[$key] = true;
            }
        }

        // remove garbage
        $this->preload = array_diff_key($this->preload, $garbage);

        if (count($this->preload) === 0) {
            return;
        }

        $pipeline = $this->redisClient()->pipeline();
        foreach ($this->preload as $key => $timestamp) {
            $pipeline->get($key); // @phpstan-ignore-line
        }
        $responses = $pipeline->execute(); // @phpstan-ignore-line

        $pkeys = array_keys($this->preload);
        // this expects count of preload and responses to be equal
        for ($i = 0; $i < count($this->preload) && $i < count($responses); $i++) {
            $key = $pkeys[$i];
            $value = $responses[$i];
            // store json
            if (is_string($value)) {
                $this->store[$key] = $value;
            } else {
                $garbage[$key] = true;
            }
        }

        // remove garbage again
        $this->preload = array_diff_key($this->preload, $garbage);
    }

    public function preloadList(): array
    {
        return $this->preload;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, mixed $value, int $minutes = 0): bool
    {
        /* SHOULD SET EVEN IN DEBUG
        if ($this->options['debug')) {
            return true;
        }
        */

        $key = $this->key($key);
        $value = (new Value($value, $minutes))->toJson();

        if ($this->options['store'] && (empty($this->options['store-ignore']) || str_contains($key, $this->options['store-ignore']) === false)) {
            $this->store[$key] = $value;
        }
        $this->preload[$key] = time();

        $method = $this->connection;
        if ($this->transaction) {
            $method = $this->transaction;
            $this->transactionsCount++;
        }

        $status = $method->set( // @phpstan-ignore-line
            $key,
            $value
        );

        if ($minutes) {
            $status = $method->expireat( // @phpstan-ignore-line
                $key,
                $this->expiration($minutes)
            );
        }

        if ($this->transactionsCount >= intval($this->options['transaction_limit'])) {
            $this->endTransaction();
            $this->beginTransaction();
        }

        return $status == 'OK' || $status == 'QUEUED';
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve(string $key): ?Value
    {
        $key = $this->key($key);

        $this->preload[$key] = time();

        $value = A::get($this->store, $key);
        $value = $value ?? $this->connection->get($key);
        // value is not in store and if transaction is open but empty
        // then it will return 'queued' even for non existing values.
        // checking if key exists does not help either.
        if ($value instanceof Status && $value->getPayload() === 'QUEUED') {
            $value = null;
        }
        $value = is_string($value) ? Value::fromJson($value) : $value;

        if ($this->options['store'] && (empty($this->options['store-ignore']) || str_contains($key, $this->options['store-ignore']) === false)) {
            $this->store[$key] = $value;
        }

        return $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->options['debug']) {
            return $default;
        }

        return parent::get($key, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function remove(string $key): bool
    {
        $key = $this->key($key);
        if (array_key_exists($key, $this->store)) {
            unset($this->store[$key]);
        }
        if (array_key_exists($key, $this->preload)) {
            unset($this->preload[$key]);
        }
        $status = $this->connection->del($key);
        if (is_int($status)) { // @phpstan-ignore-line
            return $status > 0;
        }
        if (is_string($status)) { // @phpstan-ignore-line
            return $status === 'QUEUED';
        }

        return false;
    }

    public function key(string $key): string
    {
        $option = $this->options['key'];

        return $option instanceof Closure ? $option(parent::key($key)) : '';
    }

    /**
     * {@inheritDoc}
     */
    public function flush(): bool
    {
        $this->flushstore();

        $prefix = $this->key('');
        $keys = $this->connection->keys($prefix.'*');
        if (count($keys)) {
            $this->connection->del($keys);
        }

        return true;
    }

    public function flushstore(): bool
    {
        $this->store = [];
        $this->preload = [];

        return true;
    }

    public function flushdb(): bool
    {
        return $this->connection->flushdb() == 'OK';
    }

    public function beginTransaction(): mixed
    {
        $this->transaction = $this->redisClient()->transaction();
        $this->transactionsCount = 0;

        return $this->transaction;
    }

    public function endTransaction(): void
    {
        if ($this->transaction && $this->transactionsCount > 0) {
            try {
                $this->transaction->execute(); // @phpstan-ignore-line
            } catch (\Exception $ex) {
                // TODO: ignore errors for now
                // https://redis.io/topics/transactions
                // It's important to note that even when a command fails,
                // all the other commands in the queue are processed –
                // Redis will not stop the processing of commands.
            }
        }
        $this->transactionsCount = 0;
        $this->transaction = null;
    }

    public function transactionsCount(): int
    {
        return $this->transactionsCount;
    }

    public function benchmark(int $count = 10): void
    {
        $prefix = 'redis-benchmark-';
        $redis = $this;
        $file = kirby()->cache('bnomei.redis-cachedriver'); // neat, right? ;-)

        foreach (['redis' => $redis, 'file' => $file] as $label => $driver) {
            $time = microtime(true);
            if ($label === 'redis') {
                $driver->beginTransaction();
            }
            for ($i = 0; $i < $count; $i++) {
                $key = $prefix.$i;
                if (! $driver->get($key)) {
                    $driver->set($key, Str::random(1000));
                }
            }
            for ($i = $count * 0.6; $i < $count * 0.8; $i++) {
                $key = $prefix.$i;
                $driver->remove($key);
            }
            for ($i = $count * 0.8; $i < $count; $i++) {
                $key = $prefix.$i;
                $driver->set($key, Str::random(1000));
            }
            if ($label === 'redis') {
                $this->endTransaction();
            }
            echo $label.' : '.(microtime(true) - $time).PHP_EOL;

            // cleanup
            for ($i = 0; $i < $count; $i++) {
                $key = $prefix.$i;
                $driver->remove($key);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function root(): string
    {
        $cache = kirby()->cache('bnomei.redis-cachedriver');

        return $cache instanceof FileCache ?
            $cache->root() :
            throw new \Exception('root() is only available for FileCache');
    }

    private static ?self $singleton = null;

    public static function singleton(array $options = [], array $optionsClient = []): self
    {
        if (self::$singleton === null) {
            self::$singleton = new self($options, $optionsClient);
        }

        return self::$singleton;
    }
}
