<?php
/**
 * Manages the YAMS module interface
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */

require_once( 'yams.class.inc.php' );
require_once( 'yams.error.mgr.class.inc.php' );
require_once( 'yams.lang.mgr.class.inc.php' );
require_once( 'yams.documentation.mgr.class.inc.php' );
require_once( 'templator.class.inc.php' );

if ( ! class_exists( 'YamsModuleMgr' ) )
{
  class YamsModuleMgr
  {

    // --
    // -- Public attributes
    // --

    // --
    // -- Public methods
    // --

    public function GetOutput()
    {
      $yams = YAMS::GetInstance( );
      $YEM = YamsErrorMgr::GetInstance( );
      $YLM = YamsLangMgr::GetInstance( );
      $YDM = YamsDocumentationMgr::GetInstance( );
      
      // Load the module template
      $tpl = new Templator( );
      $success = $tpl->LoadTemplateFromFile(
         'yams/module/yams.module.tpl.html'
        );
      if ( !$success )
      {
       return;
      }

      if ( $yams->IsHTTPS() )
      {
        $protocol = 'https://';
      }
      else
      {
        $protocol = 'http://';
      }
      $requestURL = $yams->Escape(
        $protocol
          . $_SERVER['SERVER_NAME']
          . $_SERVER['REQUEST_URI']
        );

      // Define the placholders
      $tpl->RegisterPlaceholder( 'form_action', '[+request_url+]' );
      $tpl->RegisterPlaceholder( 'error_messages', $YEM->GetOutput() );
      $tpl->RegisterPlaceholder( 'lang_chooser', $YLM->GetOutput() );
      $tpl->RegisterPlaceholder( 'tab_documentation', $YDM->GetOutput() );

      // Parse non-language placeholders
      $tpl->Parse( NULL, true );
      $tpl->ClearStoredPlaceholders();
      
      // Register global placeholders...
      $tpl->RegisterPlaceholder( 'request_url', $requestURL );
      $tpl->RegisterPlaceholder( 'modx_manager_theme', $modx->config['manager_theme'] );
      $tpl->RegisterPlaceholder( 'modx_site_url', $modx->config['site_url'] );
      $tpl->RegisterPlaceholder( 'modx_charset', $modx->config['modx_charset'] );
      $tpl->RegisterPlaceholder( 'yams_contact_en_url', 'http://nashi.podzone.org/en/contact.xhtml' );
      $tpl->RegisterPlaceholder( 'yams_contact_fr_url', 'http://nashi.podzone.org/fr/contact.xhtml' );
      $tpl->RegisterPlaceholder( 'yams_contact_ja_url', 'http://nashi.podzone.org/ja/contact.xhtml' );
      $tpl->RegisterPlaceholder( 'yams_donate_en_url', 'http://nashi.podzone.org/en/donate.xhtml' );
      $tpl->RegisterPlaceholder( 'yams_donate_fr_url', 'http://nashi.podzone.org/fr/donate.xhtml' );
      $tpl->RegisterPlaceholder( 'yams_donate_ja_url', 'http://nashi.podzone.org/ja/donate.xhtml' );
      $tpl->RegisterPlaceholder( 'yams_package_url', 'http://modxcms.com/extras/package/?package=543' );
      $tpl->RegisterPlaceholder( 'yams_forums_url', 'http://modxcms.com/forums/index.php/board,381.0.html' );
      $tpl->RegisterPlaceholder( 'yams_author_url', 'http://modxcms.com/forums/index.php?action=profile;u=12570' );
      $tpl->RegisterPlaceholder( 'yams_author', '<a href="[+yams_author_url+]" target="_blank">PMS</a>' );
      $tpl->RegisterPlaceholder( 'yams_copyright', '<a href="http://nashi.podzone.org/" target="_blank">Nashi Power</a> 2009' );
      $tpl->RegisterPlaceholder( 'yams_licence', 'GPL v3' );
      $tpl->RegisterPlaceholder( 'yams_version', $yams->Escape(
          $yams->GetVersion()
        ) );

      // Parse language text...
      $YLM->ParseLanguageText( $tpl );

      // Temporarily comment out this line so it's easy to find missing placeholders...
      // $tpl->RemovePlaceholdersFromTpl( NULL, true );

      return $tpl->GetTpl();

    }

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