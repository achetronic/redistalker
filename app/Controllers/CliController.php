<?php

declare( strict_types = 1 );

namespace App\Controllers;

use App\Controllers\Controller;
use Codedungeon\PHPCliColors\Color;
use DateTime;

class CliController extends Controller
{
    /**
     * 
     * 
     */
    public static function getTimestamp () : string
    {
        $now = new DateTime();
        return Color::BOLD . '[' .$now->format(DateTime::W3C) . '] ' . Color::RESET;
    }



    /**
     * 
     * 
     */
    public static function error ( string $msg = '', bool $timestamp = false)
    {
        $response = '';
        if ($timestamp){
            $response = self::getTimestamp();
        }
        $response .= Color::BG_RED . Color::BOLD . $msg . Color::RESET .PHP_EOL;
        echo $response;
    }



    /**
     * 
     * 
     */
    public static function warning ( string $msg = '', bool $timestamp = false)
    {
        $response = '';
        if ($timestamp){
            $response = self::getTimestamp();
        }
        $response .= Color::BG_BLACK . Color::YELLOW . Color::BOLD . $msg . Color::RESET .PHP_EOL;
        echo $response;
    }



    /**
     * 
     * 
     */
    public static function success ( string $msg = '', bool $timestamp = false)
    {
        $response = '';
        if ($timestamp){
            $response = self::getTimestamp();
        }
        $response .= Color::BG_BLACK . Color::GREEN . $msg . Color::RESET .PHP_EOL;
        echo $response;
    }



    /**
     * 
     * 
     */
    public static function info ( string $msg = '', bool $timestamp = false)
    {
        $response = '';
        if ($timestamp){
            $response = self::getTimestamp();
        }
        $response .= Color::BG_BLACK . Color::WHITE . $msg . Color::RESET .PHP_EOL;
        echo $response;
    }



    /**
     * 
     * 
     */
    public static function title ( string $msg = '', bool $timestamp = false)
    {
        $response = '';
        if ($timestamp){
            $response = self::getTimestamp();
        }
        $response .= Color::BG_DARK_GRAY . Color::BOLD . $msg . Color::RESET .PHP_EOL;
        echo $response;
    }

    
}