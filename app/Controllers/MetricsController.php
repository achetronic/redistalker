<?php

declare( strict_types = 1 );

namespace App\Controllers;

use App\Controllers\Controller;
use App\Controllers\CliController as Cli;
use App\Helpers\ConfigHelper as Config;
use Exception;
use App\Models\Redis;

use Predis\Commands\Pubsub;
use Predis\Commands\Llen;

class MetricsController extends Controller
{
    protected $client;

    protected ?string $queue;

    /**
     * 
     * 
     */
    public function __construct() 
    {
        $this->client = (new Redis())->getClient();

        $this->setQueue( Config::env('REDIS_QUEUE', 'pubsub_queue') );
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
     * @return string
     */
    protected function getQueue ( ) : ?string
    {
        return $this->queue;
    }

    /**
     * Get the length of the queue
     * 
     * @return int|null
     */
    protected function getQueueLen () : ?int
    {
        $len = $this->client->llen($this->getQueue());
        if( !is_int($len) ){
            return null;
        }
        return $len;
    }

    /**
     * Get total active channels
     * 
     * @return int|null
     */
    protected function getActiveChannels () : ?int
    {
        $len = $this->client->pubsub('channels');
        $len = count($len);

        if( !is_int($len) ){
            return null;
        }
        return $len;
    }

    /**
     * Get total patterns subscriptions
     * 
     * @return int|null
     */
    protected function getActivePatternSubscriptions () : ?int
    {
        $len = $this->client->pubsub('numpat');
        if( !is_int($len) ){
            return null;
        }
        return $len;
    }

    /**
     * Get all the metrics
     * 
     * @return array
     */
    public function getMetricsArray () : array
    {
        return [
            'queue_length' => $this->getQueueLen(),
            'active_channels' => $this->getActiveChannels(),
            'active_pattern_subscriptions' => $this->getActivePatternSubscriptions()
        ];
    }





}