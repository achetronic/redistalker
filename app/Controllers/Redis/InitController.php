<?php

declare( strict_types = 1 );

namespace App\Controllers\Redis;

use App\Controllers\Controller;
use App\Controllers\Redis\MonitorController;
use App\Controllers\Redis\PubsubController;

class InitController extends Controller
{
    protected $mode;
    
    /**
     * 
     * 
     */
    public function __construct( string $mode = 'pubsub') 
    {
        $this->setMode($mode);
        return $this;
    }



    /**
     * Start the loop in the defined way
     */
    public function run ()
    {
        switch($this->mode){
            case 'pubsub':
                $redis = new PubsubController;
                $redis->main();
                break;

            case 'monitor':
                $redis = new MonitorController;
                $redis->main();
                break;

            default:
                $redis = new PubsubController;
                $redis->main();
                break;
        }
    }



    /**
     * Set the desired mode to start the Redis consumer loop
     * 
     * @param string $mode The consumer mode
     * 
     * @return void
     */
    protected function setMode ( string $mode ) 
    {
        $this->mode = $mode;
        return $this;
    }

}