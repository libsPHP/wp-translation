php
<?php

namespace Tests;

use App\Cache;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{

    public function testGetCache()
    {
        // Create a new instance of the Cache class
        $cache = new Cache();

        // Test case 1: Get a key that does not exist
        $result = $cache->getCache('nonexistent_key');
        $this->assertNull($result);

        // Test case 2: Set a key and then get it
        $cache->setCache('test_key', 'test_value');
        $result = $cache->getCache('test_key');
        $this->assertEquals('test_value', $result);

        //Test case 3: Set key with integer value
        $cache->setCache('int_key', 1);
        $result = $cache->getCache('int_key');
        $this->assertEquals(1, $result);


    }
}