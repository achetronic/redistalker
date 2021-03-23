<?php

// Import all classes from Composer packages
require_once __DIR__ . '/kernel.php';

// use App\Controllers\Redis\MonitorController;
// use App\Controllers\Redis\PubsubController;

use App\Controllers\Redis\InitController;

// Execute the main flow of the monitor
// $redis = new MonitorController;
// $redis->main();

// Execute the main flow of the pubsub
// $redis = new PubsubController;
// $redis->main();

// Switch between the Redis consumer loop mode
$redis = (new InitController('pubsub'))->run();