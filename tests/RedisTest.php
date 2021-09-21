<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Bnomei\Redis;

final class RedisTest extends TestCase
{
    private $redis;

    public function getSingleton(): void
    {
        $this->redis = Redis::singleton([
            'prefix' => 'unittest',
            'host' => function () {
                return 'localhost';
            //return $_ENV['REDIS_HOST'];
            },
            'port' => function () {
                return 6379;
            //return intval($_ENV['REDIS_PORT']);
            },
        ]);
    }

    public function setUp(): void
    {
        $this->getSingleton();
        $this->redis->flushdb();
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
            'port' => function () {
                return 637900;
            },
        ]);
        $this->assertEquals(637900, $redis->option('port'));
    }

    public function testFlush()
    {
        $this->redis->flushdb();
        $this->assertNull($this->redis->get('something'));
    }

    public function testAPI()
    {
        // flush and none
        $this->redis->flushdb();
        $this->assertNull($this->redis->get('something'));

        // default
        $this->assertEquals('weird', $this->redis->get('something', 'weird'));

        // set and get infinite
        $this->assertTrue($this->redis->set('something', 'wicked'));
        $this->assertEquals('wicked', $this->redis->get('something'));

        // remove
        $this->redis->remove('something');
        $this->assertNull($this->redis->get('something'));

        // with expiration
        $this->redis->set('something', 'wobbly', 1);
        $this->assertEquals('wobbly', $this->redis->get('something'));
    }

    public function testTransaction()
    {
        $this->redis->flushdb();

        $this->redis->beginTransaction();
        for ($i = 0; $i<=5; $i++) {
            $this->redis->set('something'.$i, 'wicked'.$i);
        }
        $this->redis->endTransaction();

        $this->assertEquals('wicked5', $this->redis->get('something5'));
        // flush data in store forcing a read from db
        $this->redis->flush(); // flush not flushdb
        $this->assertEquals('wicked5', $this->redis->get('something5'));
    }

    public function testPreload()
    {
        $this->redis->flushdb();

        $this->redis->beginTransaction();
        for ($i = 0; $i<=50; $i++) {
            $this->redis->set('something'.$i, 'wicked'.$i);
        }
        $this->redis->endTransaction();

        $this->redis->flush(); // flush not flushdb
        $this->assertEquals(0, count($this->redis->preloadList()));

        // request a few and see if preload matches
        $this->assertEquals('wicked14', $this->redis->get('something14'));
        $this->assertEquals('wicked42', $this->redis->get('something42'));
        
        // last one twice should only create one record
        $this->assertEquals('wicked5', $this->redis->get('something5'));
        $this->assertEquals('wicked5', $this->redis->get('something5'));
        $this->assertEquals(3, count($this->redis->preloadList()));
    }

    public function testBenchmark()
    {
        $this->redis->flush();
        $this->redis->benchmark(1000);
        unset($this->redis); // will happen at end of pageview
        $this->assertTrue(true);
    }
}
