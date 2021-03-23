<?php

declare( strict_types = 1 );

namespace App\Controllers\Redis;

use App\Controllers\Controller;
use App\Controllers\CliController as Cli;
use App\Helpers\ConfigHelper as Config;

use Predis\Client;
use Predis\Commands\Rpush;
use Predis\Commands\Psubscribe;

class PubsubController extends Controller
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

    protected $railway;

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

        // The socket for pubsub (unidirectional)
        $this->client = new Client($this->server);

        // We need a second socket (bidirectional) to queue messages
        $this->railway = new Client($this->server);

        $this->setQueue( Config::env('REDIS_QUEUE', 'queue') );
    }



    /**
     * Init the PubSub loop, subscribe to all channels.
     * Store the messages into the queue.
     * 
     * Emergency button to break the loop
     * "PUBLISH snsorial.control stop_redistalker"
     */
    public function main ()
    {
        // Initialize a new pubsub consumer.
        $pubsub = $this->client->pubSubLoop();

        // Subscribe to your channels
        $pubsub->psubscribe('*');

        foreach ($pubsub as $message) {

            switch ($message->kind) {
                case 'psubscribe':
                    Cli::warning("Subscribed to {$message->channel}", true);
                    break;
        
                case 'pmessage':
                    if ($message->channel == 'snsorial.control') {

                        if ($message->payload == 'stop_redistalker') {
                            Cli::error("Emergency button pushed", true);
                            Cli::info('Exiting the pubsub loop...', true);
                            $pubsub->punsubscribe();
                            break;  
                        } 

                    } else {

                        $publication = [
                            'channel' => $message->channel,
                            'message' => $message->payload
                        ];
            
                        // Inform the action on the console
                        Cli::info('New on queue (' . $this->getQueue() . '): ' . json_encode($publication), true);
            
                        // Queue the message the user published
                        $this->railway->rpush($this->getQueue(), json_encode($publication));
                    }
                    break;
            }
        }

        // Always unset the pubsub consumer instance when you are done! The
        // class destructor will take care of cleanups and prevent protocol
        // desynchronizations between the client and the server.
        unset($pubsub);
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