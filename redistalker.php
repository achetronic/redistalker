<?php

// Import all classes from Composer packages
require_once __DIR__ . '/kernel.php';

use App\Controllers\RedisController;

// Execute the main flow of the project
$redis = new RedisController;
$redis->main();