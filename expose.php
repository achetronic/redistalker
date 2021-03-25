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

// $loop = React\EventLoop\Factory::create();

// echo "hola";

// $server = new React\Http\Server($loop, function (Psr\Http\Message\ServerRequestInterface $request) {
//     return new React\Http\Message\Response(
//         200,
//         array(
//             'Content-Type' => 'text/plain'
//         ),
//         "Hello World!\n"
//     );
// });

// $socket = new React\Socket\Server(8080, $loop);
// $server->listen($socket);

// echo "Server running at http://127.0.0.1:8080\n";

// $loop->run();