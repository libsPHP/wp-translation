php
<?php

/**
 * Manual Test Script for Cache Class
 *
 * This script tests the getCache method of the Cache class.
 */

require_once 'cache.php';

// Create a dummy array for testing
$dummyData = [
    'key1' => 'value1',
    'key2' => ['value2', 'value3'],
];

// Create an instance of the Cache class
$cache = new Cache();

//Set dummy data
$cache->setCache($dummyData);

// Test getCache method
$result1 = $cache->getCache('key1');
echo "Result for key1: " . print_r($result1, true) . "\n";

$result2 = $cache->getCache('key2');
echo "Result for key2: " . print_r($result2, true) . "\n";

$result3 = $cache->getCache('key3');
echo "Result for key3: " . print_r($result3, true) . "\n";
}
?>