<?php

declare( strict_types = 1 );

namespace App\Controllers\Redis;

use App\Controllers\Controller;
use App\Controllers\Redis\MonitorController;
use App\Controllers\Redis\PubsubController;
use App\Controllers\CliController as Cli;
use Exception;

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
     * 
     * 
     */
    protected function setHeader() 
    {
        Cli::title("REDISTALKER: the ultimate Redis stalker");
        Cli::warning("Consumer will run in {$this->mode} mode");
        Cli::info(PHP_EOL);
    }



    /**
     * Start the loop in the defined way
     */
    public function run ()
    {
        try{
            $this->setHeader();

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
        }catch (Exception $e){
            echo $e->getMessage();
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