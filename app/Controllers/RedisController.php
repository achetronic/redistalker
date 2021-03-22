<?php

declare( strict_types = 1 );

namespace App\Controllers;

use App\Controllers\Controller;
use Predis\Client;
use Predis\Commands\Rpush;
use DateTime;
use Codedungeon\PHPCliColors\Color;

class RedisController extends Controller
{
    protected $client;

    protected $timestamp;

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

            echo "* Received {$this->command} on DB {$this->database} at {$this->timestamp->format(DateTime::W3C)}", PHP_EOL;
            if (isset($event->arguments)) {
                echo "    Arguments: {$event->arguments}", PHP_EOL;
            }

            // If we notice a ECHO command with the message QUIT_MONITOR, we stop the
            // monitor consumer and then break the loop.
            if ($this->command === 'ECHO' && $this->arguments[0] === 'snsorial-under-attack') {
                echo 'Exiting the monitor loop...', PHP_EOL;
                echo Color::bold(), 'Hello', Color::RESET, PHP_EOL;
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
            echo "    Want to publish: " . json_encode($publication) . PHP_EOL;

            // Queue the message the user published
            $this->client->rpush($this->getQueue(), json_encode($publication));
        }
    }



    /**
     * 
     * 
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
     * 
     * 
     */
    protected function getCommand ( $event ) 
    {

        if( empty($event->command) ){
            return null;
        }

        return strtoupper($event->command);
    }



    /**
     * 
     * 
     */
    protected function getDatabase ( $event ) 
    {

        if( empty($event->database) ){
            return null;
        }

        return strtolower($event->database);
    }



    /**
     * 
     * 
     * 
     */
    protected function setQueue ( string $name ) 
    {
        $this->queue = $name;
    }



    /**
     * 
     * 
     * 
     */
    protected function getQueue ( ) 
    {
        return $this->queue;
    }
}