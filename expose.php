<?php

// Import all classes from Composer packages
require_once __DIR__ . '/kernel.php';

use App\Controllers\MetricsController;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

//
$metrics = (new MetricsController)->getMetricsArray();
var_export($metrics);

$loop = React\EventLoop\Factory::create();

$server = new React\Http\Server($loop, function (Psr\Http\Message\ServerRequestInterface $request) {

    $path = $request->getUri()->getPath();
    $method = $request->getMethod();

    if ($path === '/metrics') {
        if ($method === 'GET') {
            return new React\Http\Message\Response(200, ['Content-Type' => 'text/plain'],  'jelow metrics');
        }
    }

    return new React\Http\Message\Response(404, ['Content-Type' => 'text/plain'],  'Not found');
});

$socket = new React\Socket\Server('0.0.0.0:8080', $loop);
$server->listen($socket);

echo "Server running at http://0.0.0.0:8080\n";

$loop->run();