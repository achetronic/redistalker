<?php

// Import all classes from Composer packages
require_once __DIR__ . '/kernel.php';

use App\Controllers\Redis\MonitorController;

// Execute the main flow of the project
$redis = new MonitorController;
$redis->main();