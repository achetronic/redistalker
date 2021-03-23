<?php

declare( strict_types = 1 );

namespace App\Controllers\Redis;

use App\Controllers\Controller;
use App\Controllers\CliController as Cli;
use App\Helpers\ConfigHelper as Config;

use Predis\Client;
use Predis\Commands\Rpush;

class MonitorController extends Controller
{
    protected $server = [
        'scheme'   => 'tcp',
        'host'     => null,
        'port'     => null,
        'username' => null,
        'password' => null,
        'read_write_timeout' => 0,
    ];

    protected $client;

    protected $database;

    protected $command = null;

    protected $arguments = null;

    protected $queue = null;



    /**
     * 
     * 
     */
    public function __construct() 
    {
        $this->server['host']     = Config::env('REDIS_HOST', '127.0.0.1');
        $this->server['port']     = Config::env('REDIS_PORT', 6370);;
        $this->server['username'] = Config::env('REDIS_USERNAME', null);
        $this->server['password'] = Config::env('REDIS_PASSWORD', null);

        $this->client = new Client($this->server);

        $this->setQueue( Config::env('REDIS_QUEUE', 'queue') );
    }



    /**
     * Init the monitor loop, stalk all publish commands.
     * Store the messages into the queue.
     * 
     * Emergency button to break the loop
     * "PUBLISH snsorial.control stop_redistalker"
     */
    public function main ()
    {
        $monitor = $this->client->monitor();

        foreach ( $monitor as $event) {

            $this->database  = $this->getDatabase ( $event );
            $this->command   = $this->getCommand ( $event );
            $this->arguments = $this->getArguments ( $event );
            
            // Check if we have a command to inform about it on the console
            if( empty($this->command) ){
                continue;
            }

            if( empty($this->arguments) || count($this->arguments) > 2 ){
                continue;
            }

            Cli::warning("Received {$this->command} on DB {$this->database}", true);

            if (isset($event->arguments)) {
                Cli::info('Arguments: '.$event->arguments, true);
            }

            //
            if($this->command !== 'PUBLISH'){
                continue;
            }

            if ($this->arguments[0] === 'snsorial.control' && $this->arguments[1] === 'stop_redistalker') {
                Cli::error("Emergency button pushed", true);
                Cli::info('Exiting the monitor loop...', true);
                $monitor->stop();
                break;
            }

            // Craft the final message Redis will store
            $publication = [
                'channel' => $this->arguments[0],
                'message' => $this->arguments[1]
            ];

            // Inform the action on the console
            Cli::info('New on queue (' . $this->getQueue() . '): ' . json_encode($publication), true);

            // Queue the message the user published
            $queuePush = $this->client->rpush($this->getQueue(), json_encode($publication));
        }
    }



    /**
     * Get the arguments given to the Redis server
     * parsed as an array
     * 
     * @param $event 
     * 
     * @return array|null
     */
    protected function getArguments ( $event ) 
    {
        if( empty($event->arguments) ){
            return null;
        }

        if( !preg_match_all('/[^\"\s][^\"]*[^\"\s]*/', $event->arguments, $matches) ){
            return null;
        }

        return $matches[0];
    }



    /**
     * Get the command given to the Redis server
     * 
     * @param $event 
     * 
     * @return string|null
     */
    protected function getCommand ( $event ) 
    {

        if( empty($event->command) ){
            return null;
        }

        return strtoupper($event->command);
    }



    /**
     * Get the database given to the Redis server
     * 
     * @param $event 
     * 
     * @return string|null
     */
    protected function getDatabase ( $event ) 
    {

        if( empty($event->database) ){
            return null;
        }

        return strtolower($event->database);
    }



    /**
     * Set the queue name to queue the messages
     * 
     * @param string $name The name of the queue
     * 
     * @return void
     */
    protected function setQueue ( string $name ) 
    {
        $this->queue = $name;
    }



    /**
     * Get the queue name of the messages queue
     * 
     * @return void
     */
    protected function getQueue ( ) 
    {
        return $this->queue;
    }
}