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

    public function redisKey(string $languageCode = null): string
    {
        $key = $this->cacheId('redis');
        if (!$languageCode) {
            $languageCode = kirby()->languages()->count() ? kirby()->language()->code() : null;
            if ($languageCode) {
                $key = $languageCode . '.' . $key;
            }
        }

        return md5(kirby()->roots()->index() . $key);
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
        $key = $this->redisKey($languageCode);
        $modified = function_exists('modified') ? \modified($this) : $this->modified();
        $modifiedCache = Redis::getSingleton()->redisClient()->exists($key.'-modified') ?
            Redis::getSingleton()->redisClient()->get($key.'-modified') : null;
        if ($modifiedCache && intval($modifiedCache) < intval($modified)) {
            return null;
        }
        $data = Redis::getSingleton()->redisClient()->exists($key) ?
            Redis::getSingleton()->redisClient()->get($key) : null;
        return $data ? json_decode($data, true) : null;
    }

    public function writeContent(array $data, string $languageCode = null): bool
    {
        // write to file and redis
        return parent::writeContent($data, $languageCode) &&
            $this->writeContentRedis($data, $languageCode);
    }

    public function writeContentRedis(array $data, string $languageCode = null): bool
    {
        $key = $this->redisKey($languageCode);
        $modified = function_exists('modified') ? \modified($this) : $this->modified();
        Redis::getSingleton()->redisClient()
            ->set($key.'-modified', $modified);
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
            ->del($this->redisKey());
        Redis::getSingleton()->redisClient()
            ->del($this->redisKey().'-modified');

        foreach(kirby()->languages() as $language) {
            Redis::getSingleton()->redisClient()
                ->del($this->redisKey($language->code()));
            Redis::getSingleton()->redisClient()
                ->del($this->redisKey($language->code()).'-modified');
        }
    }
}
