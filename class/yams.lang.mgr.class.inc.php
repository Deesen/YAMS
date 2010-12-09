<?php
/**
 * A simple class that manages multlingual text
 * Languages are defined in the /lang/*lang*.inc.php files
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */

require_once( 'templator.class.inc.php');

define('YAMSLANGMGR_RE_LANG','[a-z][a-z-0-9]+');

if ( ! class_exists( 'YamsLangMgr' ) )
{
  class YamsLangMgr
  {

    public function IsAvailableLang( $lang )
    {
      if ( in_array( $lang, $this->itsAvailableLangs ) )
      {
        return true;
      }
      return false;
    }

//    public function GetCurrentTimeStampFormat()
//    {
//      return $this->itsCurrentTimeStampFormat;
//    }
//
    public function GetCurrentLangDir()
    {
      return $this->itsCurrentLangDir;
    }

    public function GetCurrentLangName()
    {
      return $this->itsCurrentLangName;
    }

    public function GetOutput()
    {
      $yams = YAMS::GetInstance();
      $langChooserOptions = '';

      foreach ( $this->itsAvailableLangs as $lang )
      {
        if ( $lang == $this->itsCurrentLang )
        {
          $selected = 'selected="selected"';
        }
        else
        {
          $selected = '';
        }

        $langDetails = $this->GetLangDetails( $lang );
        
        $cleanName = $yams->Clean(
          strip_tags( $langDetails['name'] )
          );
        $langDir = $yams->Clean(
          strip_tags( $langDetails['dir'] )
          );

        $langChooserOptions .= '<option ' . $selected . ' value="' . $lang . '" dir="' . $langDir . '">' . $cleanName . '</option>';
      }

      $tpl = new Templator();
      $tpl->LoadTemplateFromFile('yams/module/yams.lang.chooser.tpl.html');
      $tpl->RegisterPlaceholder('lang_chooser_options', $langChooserOptions );
      $tpl->RegisterPlaceholder('lang_post_param', $yams->Escape(
          $this->itsGetLangPostParam
          ) );
      return $tpl->Parse( );
      
    }

    public function ParseLanguageText( Templator &$tpl )
    {
      // Parses a template, resolving all language specific placeholders
      // Parses in the current language first, then the default language after
      
      if ( !$tpl->IsTemplateLoaded() )
      {
        throw new Exception('YamsLangMgr: A template must be loaded in before parsing the language text');
      }
      
      $parseCurrent = true;

      if ( $this->itsDefaultLang == $this->itsCurrentLang )
      {
        $parseCurrent = false;
      }
      else
      {        
        $currentLangFile = $this->itsCurrentLang . '.inc.php';
        $currentLangPath = $this->itsLangDir . $currentLangFile;
        if ( ! is_file( $currentLangPath ) )
        {
          if ( ! is_file( $currentLangPath ) )
          {
            throw new Exception('YamsLangMgr: Could not load current language file: ' . $currentLangPath );
          }
        }
      }
      
      $defaultLangFile = $this->itsDefaultLang . '.inc.php';
      $defaultLangPath = $this->itsLangDir . $defaultLangFile;
      if ( ! is_file( $defaultLangPath ) )
      {
        throw new Exception('YamsLangMgr: Could not load default language file: ' . $defaultLangPath );
      }

      $removeUnrecognisedPlaceholders = $tpl->IsRemoveUnrecognisedPlaceHolders();
      $tpl->SetRemoveUnrecognisedPlaceHolders( false );
      
      // Register the placeholders
      if ( $parseCurrent )
      {
        require( $currentLangPath );
        $tpl->Parse( NULL, true );
      }

      require( $defaultLangPath );
      $tpl->Parse( NULL, true );

      // restore the placeholder removal mode
      $tpl->SetRemoveUnrecognisedPlaceHolders( $removeUnrecognisedPlaceholders );
      
    }

    public function GetCurrentLang()
    {
      return $this->itsCurrentLang;
    }

    public function UpdateCurrentLang()
    {
      $lang = Null;
      if ( isset( $_GET[ $this->itsGetLangGetParam ] ) )
      {
        die('here 1');
        $lang = $_GET[ $this->itsGetLangGetParam ];
        if ( preg_match('/^' . YAMSLANGMGR_RE_LANG . '$/u', $lang ) != 1 )
        {
          return false;
        }
        $this->SetCurrentLang( $lang );
        return true;
      }

      if ( isset( $_POST[ $this->itsGetLangPostParam ] ) )
      {
        $lang = $_POST[ $this->itsGetLangPostParam ];
        if ( preg_match('/^' . YAMSLANGMGR_RE_LANG . '$/u', $lang ) != 1 )
        {
          return false;
        }
        $this->SetCurrentLang( $lang );
        return true;
      }

      if ( isset( $_COOKIE[ $this->itsGetLangCookieParam ] ) )
      {
        $lang = $_COOKIE[ $this->itsGetLangCookieParam ];
        if ( preg_match('/^' . YAMSLANGMGR_RE_LANG . '$/u', $lang ) != 1 )
        {
          return false;
        }
        $this->SetCurrentLang( $lang );
        return true;
      }

      $this->SetCurrentLang( $this->itsCurrentLang );
      return true;
    }

    // --
    // -- Private Methods
    // --

    private function GetLangDetails( $lang )
    {
      if ( ! in_array( $lang, $this->itsAvailableLangs ) )
      {
        throw new Exception('YamsLangMgr: Cannot get details since specified lang is unavailable. ' . $lang );
      }
      $langFile = $lang . '.inc.php';
      $langPath = $this->itsLangDir . $langFile;
      if ( ! is_file( $langPath ) )
      {
        throw new Exception('YamsLangMgr: Could not load language file: ' . $langPath );
      }
      // This defines $langName
      require( $langPath );

      $langDetails = array();

      if ( ! isset( $langName ) )
      {
        throw new Exception('YamsLangMgr: The language file does not define the language name, langName.');
      }
      $langDetails['name'] = $langName;
      if ( ! isset( $langDir ) )
      {
        throw new Exception('YamsLangMgr: The language file does not define the language direction.');
      }
      switch ( $langDir )
      {
        case 'ltr':
        case 'rtl':
          break;
        default:
          throw new Exception('YamsLangMgr: Invalid language direction encountered in ' . $langFile . '. Should be ltr or rtl.');
      }
      $langDetails['dir'] = $langDir;
//      if ( ! isset( $langTimeStampFormat ) )
//      {
//        throw new Exception('YamsLangMgr: The language file does not define the time stamp format, langTimeStampFormat.');
//      }
//      $langDetails['timestamp_format'] = $langTimeStampFormat;

      return $langDetails;
    }

    private function UpdateLangDetails( )
    {
      $langDetails = $this->GetLangDetails( $this->itsCurrentLang );
      $this->itsCurrentLangName = $langDetails['name'];
      $this->itsCurrentLangDir = $langDetails['dir'];
      // $this->itsCurrentTimeStampFormat = $langDetails['timestamp_format'];
      return true;
    }

    private function SetCurrentLang( $lang )
    {
      if ( ! in_array( $lang, $this->itsAvailableLangs ) )
      {
        return false;
      }
      $this->itsCurrentLang = $lang;
      $success =  setcookie(
        $this->itsGetLangCookieParam
        , $this->itsCurrentLang
        , time() + 604800
        , '/'
        );
      $this->UpdateLangDetails();
      return $success;
    }

    private function UpdateAvailableLangs()
    {

      $langFiles = scandir( $this->itsLangDir );
      if ( $langFiles === false )
      {
        throw new Exception( 'YamsLangMgr: Could not read language directory');
      }
      $this->itsAvailableLangs = array();
      foreach ( $langFiles as $langFile )
      {        
        if ( preg_match( '/^(' . YAMSLANGMGR_RE_LANG . ')\.inc\.php$/', $langFile, $matches ) == 1 )
        {
          $this->itsAvailableLangs[] = $matches[1];
        }
      }
      if ( count( $this->itsAvailableLangs ) == 0 )
      {
        throw new Exception('YamsLangMgr: Please install a language file to .' . $this->itsLangDir );
      }
      
    }

    private function Initialise()
    {
      $this->itsLangDir = dirname( __FILE__ ) . '/../lang/';
      if ( ! is_dir( $this->itsLangDir ) )
      {
        throw new Exception( 'YamsLangMgr: Could not find the lang directory: ' . $this->itsLangDir );
      }

      $this->UpdateAvailableLangs();

      if ( ! in_array( $this->itsDefaultLang, $this->itsAvailableLangs ) )
      {
        throw new Exception('YamsLangMgr: There is no language file associated with the default language - ' . $this->itsDefaultLang . '. Found: ' . implode(',  ',  $this->itsAvailableLangs) );
      }
      $this->itsCurrentLang = $this->itsDefaultLang;

      $this->UpdateCurrentLang();

    }

    // --
    // -- Private attributes
    // --

    private $itsCurrentLang = NULL;
    private $itsLangDir = NULL;
    private $itsAvailableLangs = array();
    private $itsCurrentLangName = NULL;
    private $itsCurrentLangDir = NULL;
    // private $itsCurrentTimeStampFormat = NULL;
    
    private $itsDefaultLang = 'english-british';
    private $itsGetLangGetParam = 'yams_module_lang';
    private $itsGetLangPostParam = 'yams_module_lang';
    private $itsGetLangCookieParam = 'yams_module_lang';

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