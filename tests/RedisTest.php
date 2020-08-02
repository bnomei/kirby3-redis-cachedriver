<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Bnomei\Redis;

final class RedisTest extends TestCase
{
    private $redis;

    public function setUp(): void
    {
        $this->redis = new Redis([
            'prefix' => 'unittest',
            'host' => function() {
                return intval($_ENV['REDIS_HOST']);
            },
            'port' => function() {
                return intval($_ENV['REDIS_PORT']);
            },
        ]);
        $this->redis->flush();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Redis::class, $this->redis);
    }

    public function testClient()
    {
        $this->assertInstanceOf(Predis\Client::class, $this->redis->redisClient());
    }

    public function testOption()
    {
        $this->assertIsArray($this->redis->option());
        $this->assertEquals(6379, $this->redis->option('port'));
    }

    public function testCallableOption()
    {
        $redis = new Redis([
            'port' => function() {
                return 637900;
            },
        ]);
        $this->assertEquals(637900, $redis->option('port'));
    }

    public function testFlush()
    {
        $this->redis->flush();
        $this->assertNull($this->redis->get('something'));
    }

    public function testAPI()
    {
        // flush and none
        $this->redis->flush();
        $this->assertNull($this->redis->get('something'));

        // default
        $this->assertEquals('weird', $this->redis->get('something', 'weird'));

        // set and get infinite
        $this->redis->set('something', 'wicked');
        $this->assertEquals('wicked', $this->redis->get('something'));

        // remove
        $this->redis->remove('something');
        $this->assertNull($this->redis->get('something'));

        // with expiration
        $this->redis->set('something', 'wobbly',1);
        $this->assertEquals('wobbly', $this->redis->get('something'));
    }
}
