<?php

// Import all classes from Composer packages
require_once __DIR__ . '/kernel.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Controllers\WebserverController;

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

//
(new WebserverController)->main();