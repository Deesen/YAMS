<?php

if ( ! class_exists( 'Singleton') )
{
  interface ISingleton
  {
    public static function GetInstance();
  }

  abstract class Singleton implements ISingleton
  {

    // --
    // -- Private Methods
    // --

    abstract protected function Initialise( );

    // --
    // -- Singleton stuff
    // --

    // A private constructor; prevents direct creation of object
    protected function __construct( )
    {
      $this->Initialise();
    }

    // The singleton method
    protected static function GetSingletonInstance( $c )
    {
      static $theirInstances = array();

      if (! array_key_exists($c, $theirInstances) )
      {
        $theirInstances[ $c ] = new $c();
      }

      return $theirInstances[ $c ];
    }

    protected final function __clone()
    {
      throw new Exception('Clone is not allowed on singleton.');
    }

  }

}

?>