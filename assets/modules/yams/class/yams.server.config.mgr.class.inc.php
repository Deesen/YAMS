<?php
/**
 * Manages the Server Config tab of the YAMS module interface
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */

if ( ! class_exists( 'YamsServerConfigMgr' ) )
{
  class YamsServerConfigMgr
  {

    // --
    // -- Public attributes
    // --

    // --
    // -- Public methods
    // --

    // --
    // -- Private methods
    // --
    
    private function Initialise()
    {
    }

    // --
    // -- Private attributes
    // --

    // --
    // -- Singleton stuff
    // --

    // A private constructor; prevents direct creation of object
    private function __construct( )
    {
      $this->Initialise( );
    }

    // The singleton method
    public static function GetInstance( )
    {
      if ( ! isset( self::$theirInstance ) )
      {
        $c = __CLASS__;
        self::$theirInstance = new $c( );
      }

      return self::$theirInstance;
    }

    private final function __clone()
    {
      throw new Exception('Clone is not allowed on singleton.');
    }

    // Hold an instance of the class
    private static $theirInstance;
  }

}

?>