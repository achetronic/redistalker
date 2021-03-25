<?php

declare( strict_types = 1 );

namespace App\Models;

use Exception;
use Predis\Client;
use App\Helpers\ConfigHelper as Config;

class Redis
{
    protected ?string $scheme = null;
    protected ?string $host = null;
    protected ?int    $port = null;
    protected ?string $username = null;
    protected ?string $password = null;
    protected ?int    $timeout = null;

    protected array $server;

    protected Client $client;



    /**
     * 
     * 
     */
    public function __construct(
        ?string $scheme = null,
        ?string $host = null,
        ?int    $port = null,
        ?string $username = null,
        ?string $password = null,
        ?int    $timeout = null
    ) 
    {
        $this->setScheme($scheme);
        $this->setHost($host);
        $this->setPort($port);
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setTimeout($timeout);

        $this->client = new Client($this->getServer());

        if ( empty($this->client) ) throw new Exception('[ERROR] ('.__CLASS__.'): The client was not created.');
    }



    /**
     * Set the scheme for the connection
     * 
     * @param string $value The value of the scheme
     * 
     * @return void
     */
    protected function setScheme ( ?string $value ) : void
    {
        if( empty($value) ){
            $this->scheme = Config::env('REDIS_SCHEME', 'tcp');
            return;
        }
        $this->scheme = $value;
    }

    /**
     * Set the host for the connection
     * 
     * @param string $value The value of the host
     * 
     * @return void
     */
    protected function setHost ( ?string $value ) : void
    {
        if( empty($value) ){
            $this->host = Config::env('REDIS_HOST', '127.0.0.1');
            return;
        }
        $this->host = $value;
    }

    /**
     * Set the port for the connection
     * 
     * @param int $value The value of the port
     * 
     * @return void
     */
    protected function setPort ( ?int $value ) : void
    {
        if( empty($value) ){
            $this->port = intval(Config::env('REDIS_PORT', 6379));
            return;
        }
        $this->port = $value;
    }

    /**
     * Set the username for the connection
     * 
     * @param string $value The name of the user
     * 
     * @return void
     */
    protected function setUsername ( ?string $value ) : void
    {
        if( empty($value) ){
            $this->username = Config::env('REDIS_USERNAME', null);
            return;
        }
        $this->username = $value;
    }

    /**
     * Set the password for the connection
     * 
     * @param string $value The value of the password
     * 
     * @return void
     */
    protected function setPassword ( ?string $value ) : void
    {
        if( empty($value) ){
            $this->password = Config::env('REDIS_PASSWORD', null);
            return;
        }
        $this->password = $value;
    }

    /**
     * Set the timeout for the connection
     * 
     * @param int $value The value of the timeout
     * 
     * @return void
     */
    protected function setTimeout ( ?int $value ) : void
    {
        if( empty($value) ){
            $this->timeout = Config::env('REDIS_TIMEOUT', 0);
            return;
        }
        $this->timeout = $value;
    }

    /**
     * Get self atributes joined as an array
     * 
     * @return array
     */
    protected function getServer () : array
    {
        return [
            'scheme'   => $this->scheme,
            'host'     => $this->host,
            'port'     => $this->port,
            'username' => $this->username,
            'password' => $this->password,
            'read_write_timeout' => $this->timeout,
        ];
    }

    /**
     * Get the pointer to the connection with Redis
     * 
     * @return null|\Predis\Client
     */
    public function getClient () : ?\Predis\Client
    {
        if( empty($this->client) ){
            return null;
        }
        return $this->client;
    }

}