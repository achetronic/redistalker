<?php

declare( strict_types = 1 );

namespace App\Controllers;

use Exception;
use App\Controllers\Controller;
use App\Controllers\PrometheusController;
use App\Helpers\ConfigHelper as Config;

use React\EventLoop\Factory;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use React\Http\Message\Response as HttpResponse;
use Psr\Http\Message\ServerRequestInterface as HttpRequest;

class WebserverController extends Controller
{
    protected $ip;

    protected $port;


    /**
     * 
     * 
     */
    public function __construct() 
    {
        $this->setIp( Config::env('REACTPHP_IP', '0.0.0.0') );
        $this->setPort( Config::env('REACTPHP_PORT', '80') );
    }

    /**
     * Set the IP to bind the webserver
     * 
     * @param string $value The value of the ip
     * 
     * @return void
     */
    protected function setIp ( string $value ) 
    {
        $this->ip = $value;
    }

    /**
     * Get the IP value of the webserver
     * 
     * @return string
     */
    protected function getIp ( ) : ?string
    {
        return $this->ip;
    }

    /**
     * Set the port to bind the webserver
     * 
     * @param string $value The value of the port
     * 
     * @return void
     */
    protected function setPort ( string $value ) 
    {
        $this->port = $value;
    }

    /**
     * Get the port value of the webserver
     * 
     * @return string
     */
    protected function getPort ( ) : ?string
    {
        return $this->port;
    }

    /**
     * Start the web server
     * 
     * @return void
     */
    public function main()
    {
        $loop = Factory::create();

        $server = new HttpServer($loop, function (HttpRequest $request) {

            $path = $request->getUri()->getPath();
            $method = $request->getMethod();

            if ($path === '/metrics') {
                if ($method === 'GET') {
                    return new HttpResponse(
                        200, 
                        ['Content-Type' => 'text/plain'],  
                        (new PrometheusController)->renderMetrics()
                    );
                }
            }

            if ($path === '/healthz') {
                if ($method === 'GET') {
                    return new HttpResponse(
                        200, 
                        ['Content-Type' => 'text/plain'],  
                        'Work in progress'
                    );
                }
            }

            return new HttpResponse(404, ['Content-Type' => 'text/plain'],  'Not found');
        });

        $socket = new SocketServer($this->getIp().':'.$this->getPort(), $loop);
        $server->listen($socket);

        echo "Metrics server running at http://".$this->getIp().':'.$this->getPort() . PHP_EOL;
        echo "Two endpoints available: ". PHP_EOL;
        echo "/metrics instrumented for Prometheus". PHP_EOL;
        echo "/healthz instrumented for Docker/Kubernetes". PHP_EOL;

        $loop->run();
    }





}