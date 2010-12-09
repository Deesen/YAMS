<?php
/**
 * Manages global error messages for display in the YAMS module interface
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */

require_once( 'templator.class.inc.php' );

if ( ! class_exists( 'YamsErrorMgr' ) )
{
  class YamsErrorMgr
  {

    // --
    // -- Public attributes
    // --

    // --
    // -- Public methods
    // --

    public function GetOutput()
    {
      $yams = YAMS::GetInstance();
      $errorList = '';
      if ( count( $this->itsErrorMessages ) > 0 )
      {
        $errorList .= '<ul>';
        foreach ( $this->itsErrorMessages as $msg )
        {
          $errorList .=
            '<li>'
            . $yams->Escape( $msg )
            . '</li>';
        }
        $errorList .= '</ul>';
      }
      
      $tpl = new Templator();
      $tpl->LoadTemplateFromFile( 'yams/module/yams.error.tpl.html' );
      $tpl->RegisterPlaceholder('error_list', $errorList );

      return $tpl->Parse();
    }

    public function AddErrorMessage( $msg )
    {
      $this->itsErrorMessages[] = $msg;
      return true;
    }

    public function Reset()
    {
      return $this->Initialise();
    }

    // --
    // -- Private methods
    // --
    
    private function Initialise()
    {
      $this->itsErrorMessages = array();
      return true;
    }

    // --
    // -- Private attributes
    // --

    private $itsErrorMessages = array();

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