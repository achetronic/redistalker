<?php

declare( strict_types = 1 );

namespace App\Helpers;

class ConfigHelper 
{
    /**
     * Get a value fron ENV vars or return a default value
     * 
     * @param string $var 
     * @param $default
     */
    public static function env ( $var, $default )
    {
        if( empty($_SERVER[$var]) || $_SERVER[$var] == 'null' )
            return $default;

        return $_SERVER[$var];
    }
}