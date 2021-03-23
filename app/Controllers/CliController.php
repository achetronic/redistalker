<?php

declare( strict_types = 1 );

namespace App\Controllers;

use App\Controllers\Controller;
use Codedungeon\PHPCliColors\Color;

class CliController extends Controller
{
    /**
     * 
     * 
     */
    public static function error ( string $msg ) : string
    {
        $msg = Color::BG_RED . Color::BOLD . $msg . Color::RESET .PHP_EOL;
        echo $msg;
        return $msg;
    }



    /**
     * 
     * 
     */
    public static function warning ( string $msg ) : string
    {
        $msg = Color::BG_BLACK . Color::YELLOW . Color::BOLD . $msg . Color::RESET .PHP_EOL;
        echo $msg;
        return $msg;
    }



    /**
     * 
     * 
     */
    public static function success ( string $msg ) : string
    {
        $msg = Color::BG_BLACK . Color::GREEN . $msg . Color::RESET .PHP_EOL;
        echo $msg;
        return $msg;
    }



    /**
     * 
     * 
     */
    public static function info ( string $msg ) : string
    {
        $msg = Color::BG_BLACK . Color::WHITE . $msg . Color::RESET .PHP_EOL;
        echo $msg;
        return $msg;
    }

    
}