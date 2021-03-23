<?php

declare( strict_types = 1 );

namespace App\Controllers\Redis;

use App\Controllers\Controller;
use Predis\Client;
use Predis\Commands\Rpush;
use DateTime;
use App\Controllers\CliController as Cli;

class MonitorController extends Controller
{
    protected $client;

    protected DateTime $timestamp;

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
        $this->client = new Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ] + array('read_write_timeout' => 0));

        // Use only one instance of DateTime, we will update the timestamp later.
        $this->timestamp = new DateTime();

        $this->setQueue('cola');
    }



    /**
     * 
     * 
     */
    public function main ()
    {
        $monitor = $this->client->monitor();

        foreach ( $monitor as $event) {

            $this->timestamp->setTimestamp((int) $event->timestamp);
            // $this->timestamp = (string) $this->timestampObj->format(DateTime::W3C);

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

            // If we notice a ECHO command with the message QUIT_MONITOR, we stop the
            // monitor consumer and then break the loop.
            if ($this->command === 'ECHO' && $this->arguments[0] === 'snsorial-under-attack') {
                Cli::error("Emergency button pushed", true);
                Cli::info('Exiting the monitor loop...', true);
                $monitor->stop();
                break;
            }

            //
            if($this->command !== 'PUBLISH'){
                continue;
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

            // var_dump($queuePush);
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