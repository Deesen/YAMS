<?php
/**
 * A simple class that does templating using [+some_name+] style placeholders
 * Allows mutlilanguage text to be placed in placeholders.
 * Allows the separatation of code and html
 * Used to build up the module interface
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */

define(
  'TEMPLATOR_RE_NAME'
  , '[a-zA-Z0-9_]+'
);
define(
  'TEMPLATOR_RE_START'
  , '\[\+'
);
define(
  'TEMPLATOR_RE_END'
  , '\+\]'
);
define(
  'TEMPLATOR_RE_PH'
  , TEMPLATOR_RE_START
  . '('
  . TEMPLATOR_RE_NAME
  . ')'
  . TEMPLATOR_RE_END
  );

if ( ! class_exists( 'Templator' ) )
{
  class Templator
  {

    // --
    // -- Public Methods
    // --

    public function IsRemoveUnrecognisedPlaceHolders()
    {
      return $this->itsRemoveUnrecognisedPlaceHolders;
    }

    public function SetRemoveUnrecognisedPlaceHolders( $removeUnrecognisedPlaceholders )
    {
      if ( $removeUnrecognisedPlaceholders )
      {
        $this->itsRemoveUnrecognisedPlaceHolders = true;
      }
      else
      {
        $this->itsRemoveUnrecognisedPlaceHolders = false;
      }
      return true;
    }

    public function IsTemplateLoaded()
    {
      return $this->itsIsTplLoaded;
    }

    public function LoadTemplate( $tpl )
    {
      if ( !is_string( $tpl ) )
      {
        return false;
      }
      $this->itsTpl = $tpl;
      
      // Register the placeholder names
      $count = preg_match_all(
        '/' . TEMPLATOR_RE_PH . '/u'
        , $tpl
        , $matches
        , PREG_PATTERN_ORDER
      );
      // $this->itsTplPlaceHolderNames = $matches[1];

      $this->itsIsTplLoaded = true;

      return true;
    }
    
    public function LoadTemplateFromFile( $tplFile )
    {
      // $tplFile is the path to a template file relative to and beneath the
      // tpl directory

      if ( $tplFile == '' )
      {
        return false;
      }

      $realPathToTplFile = realpath( $this->itsTplDir . $tplFile );
      if ( $realPathToTplFile === false )
      {
        return false;
      }

      $realPathToTplDir = realpath( $this->itsTplDir );
      if ( $realPathToTplDir === false )
      {
        return false;
      }

      $isInSubdirectory = preg_match(
        '/^' . preg_quote( $realPathToTplDir, '/' ) . '/u'
        , $realPathToTplFile
        );
      if ( ! $isInSubdirectory )
      {
        return false;
      }
      
      $tpl = file_get_contents( $realPathToTplFile );
      if ( $tpl === false )
      {
        return false;
      }
      
      return $this->LoadTemplate( $tpl );

    }

    public function RegisterPlaceholder( $name, $value, $overwrite = TRUE )
    {
      if ( !is_string( $name ) || !is_string( $value ) )
      {
        return false;
      }
      $count = preg_match(
        '/^' . TEMPLATOR_RE_NAME . '$/u'
        , $name
        );
      if ( $count != 1 )
      {
        throw new Exception('Templator: Invalid placeholder name specified: ' . $name);
      }
      if ( array_key_exists( $name, $this->itsPlaceHolders ) )
      {
        if ( ! $overwrite )
        {
          throw new Exception('Templator: This placholder has already been defined, but placeholder overwriting is not enabled: ' . $name);
        }
      }
      $this->itsPlaceHolders[ $name ] = $value;
      return true;
    }

    public function Parse( $tpl = NULL, $updateTpl = false )
    {
      if ( !is_string( $tpl ) )
      {
        if ( ! $this->itsIsTplLoaded )
        {
          return false;
        }
        $tpl = $this->itsTpl;
      }
      $this->itsCounter++;
      if ( $this->itsCounter > $this->itsRecursionLimit )
      {
        $this->itsCounter--;
        return $tpl;
      }
      $result = preg_replace_callback(
          '/' . TEMPLATOR_RE_PH .'/u'
          , array( $this, 'ParseCallback' )
          , $tpl
          , -1
          );
      if ( $updateTpl )
      {
        $this->itsTpl = $result;
      }
      $this->itsCounter--;
      return $result;
//      return preg_replace_callback(
//        '/' . TEMPLATOR_RE_PH .'/u'
//        , array( $this, 'ParseCallback' )
//        , $tpl
//        , -1
//        );
    }

    public function RemovePlaceholdersFromTpl( $tpl = NULL, $updateTpl = false )
    {

      if ( !is_string( $tpl ) )
      {
        if ( ! $this->itsIsTplLoaded )
        {
          return false;
        }
        $tpl = $this->itsTpl;
      }

      $result = preg_replace(
          '/' . TEMPLATOR_RE_PH .'/u'
          , ''
          , $tpl
          , -1
        );

      if ( $updateTpl )
      {
        $this->itsTpl = $result;
      }
      return $result;
    }

    public function GetTpl()
    {
      // returns the currently stored template
      return $this->itsTpl;
    }

    public function ClearAll()
    {
      $this->itsTpl = '';
      // $this->itsTplPlaceHolderNames = array();
      $this->itsPlaceHolders = array();
      $this->itsIsTplLoaded = false;
      $this->itsCounter = 0;
    }

    public function ClearStoredPlaceholders()
    {
      $this->itsPlaceHolders = array();
      $this->itsCounter = 0;
    }

    // A private constructor; prevents direct creation of object
    public function __construct( $removeUnrecognisedPlaceHolders = false )
    {
      $this->itsTplDir = dirname( __FILE__ ) . '/../tpl/';

      $this->itsRemoveUnrecognisedPlaceHolders = $removeUnrecognisedPlaceHolders;

      $this->ClearAll();
    }

    // --
    // -- Private Methods
    // --

    private function ParseCallback( $matches )
    {
      $name = $matches[1];
      if ( array_key_exists( $name, $this->itsPlaceHolders ) )
      {
        return $this->Parse( $this->itsPlaceHolders[ $name ] );
      }
      if ( $this->itsRemoveUnrecognisedPlaceHolders )
      {
        return '';
      }
      return $matches[0];
    }

    // --
    // -- Private attributes
    // --

    private $itsTplDir = '';
    private $itsTpl = '';
    // private $itsTplPlaceHolderNames = array();
    private $itsPlaceHolders = array();
    private $itsIsTplLoaded = false;
    private $itsCounter = 0;
    private $itsRecursionLimit = 30;
    private $itsRemoveUnrecognisedPlaceHolders = true;
    
  }
}
?>