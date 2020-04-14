<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cms\Page;

class RedisPage extends Page
{
    public function dbsize(): int
    {
        return Redis::getSingleton()->redisClient()->dbsize();
    }

    public function readContent(string $languageCode = null): array
    {
        // read from redis if exists
        $data = $this->readContentRedis($languageCode);
        if (\option('debug') && $data) {
            $this->deleteRedis();
            $data = null;
        }

        // read from file and update redis
        if (! $data) {
            $data = parent::readContent($languageCode);
            $this->writeContentRedis($data, $languageCode);
        }

        return $data;
    }

    public function readContentRedis(string $languageCode = null): ?array
    {
        $key = $this->cacheId('redis');
        return Redis::getSingleton()->redisClient()->exists($key) ?
            json_decode(Redis::getSingleton()->redisClient()->get($key), true) :
            null;
    }

    public function writeContent(array $data, string $languageCode = null): bool
    {
        // write to file and redis
        return parent::writeContent($data, $languageCode) &&
            $this->writeContentRedis($data, $languageCode);
    }

    public function writeContentRedis(array $data, string $languageCode = null): bool
    {
        $key = $this->cacheId('redis'); // uses $languageCode
        return Redis::getSingleton()->redisClient()
                ->set($key, json_encode($data)) == 'OK';
    }

    public function delete(bool $force = false): bool
    {
        $this->deleteRedis();

        return parent::delete($force);
    }

    private function deleteRedis(): void
    {
        Redis::getSingleton()->redisClient()
            ->del($this->cacheId('redis'));
    }
}
