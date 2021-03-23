<?php

declare( strict_types = 1 );

namespace App\Helpers;

class ConfigHelper 
{
    /**
     * Get a value fron ENV vars or return a default value
     * 
     */
    public static function env ( $var, $default )
    {
        if( empty($_ENV[$var]) || $_ENV[$var] == 'null' )
            return $default;

        return $_ENV[$var];
    }



    /**
     * 
     * 
     */
    public static function config ($file)
    {

    }
}