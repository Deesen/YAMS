<?php
/**
 * Manages the Documenation tab of the YAMS module interface
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */
require_once( 'templator.class.inc.php' );
require_once( dirname( __FILE__ ) . '/../yams.module.funcs.inc.php' );

if ( ! class_exists( 'YamsDocumentationMgr' ) )
{
  class YamsDocumentationMgr
  {

    // --
    // -- Public attributes
    // --

    function GetOutput()
    {
      // Load the documentation tab template
      $tpl = new Templator( );
      $success = $tpl->LoadTemplateFromFile(
         'yams/module/yams.tab.documentation.tpl.html'
        );
      if ( !$success )
      {
       return '';
      }

      // Load the about sub-tab template
      $aboutTpl = new Templator( );
      $success = $aboutTpl->LoadTemplateFromFile(
         'yams/module/yams.tab.documentation.about.tpl.html'
        );
      if ( !$success )
      {
       return '';
      }      
      $tpl->RegisterPlaceholder('tab_documentation_about', $aboutTpl->Parse( NULL, false ) );
      unset( $aboutTpl );

      // Load the rows of the set-up table
      $setupTableTpl = new Templator( );
      $success = $setupTableTpl->LoadTemplateFromFile(
         'yams/module/yams.tab.documentation.setup.table.body.row.tpl.html'
        );
      if ( !$success )
      {
       return '';
      }
      $setupTableRows = '';

      YamsAlternateRow( $rowClass );
      $step = 1;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_backup_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_backup_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_urlformat_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_urlformat_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_langsettings_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_langsettings_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_friendlyurls_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_friendlyurls_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_updateurls_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_updateurls_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_updatetags_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_updatetags_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_updatesnippets_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_updatesnippets_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_redirection_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_redirection_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_managermanager_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_managermanager_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_multilingualtpl_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_multilingualtpl_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_translate_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_translate_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_publicise_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_publicise_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      YamsAlternateRow( $rowClass );
      $step++;
      $setupTableTpl->RegisterPlaceholder( 'row_class', $rowClass );
      $setupTableTpl->RegisterPlaceholder( 'step', strval( $step ) );
      $setupTableTpl->RegisterPlaceholder( 'action_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_done_action+]' );
      $setupTableTpl->RegisterPlaceholder( 'description_text', '[+txt_tab_documentation_setup_fromscratch_table_heading_row_done_description+]' );
      $setupTableRows .= $setupTableTpl->Parse( NULL, false );
      $setupTableTpl->ClearStoredPlaceholders();

      // Load the setup sub-tab template
      $setupTpl = new Templator( );
      $success = $setupTpl->LoadTemplateFromFile(
         'yams/module/yams.tab.documentation.setup.tpl.html'
        );
      if ( !$success )
      {
       return '';
      }
      $tpl->RegisterPlaceholder('tab_documentation_setup', $setupTpl->Parse( NULL, false ) );
      $tpl->RegisterPlaceholder('tab_documentation_setup_fromscratch_table_body_rows', $setupTableRows );
      unset( $setupTpl );

      return $tpl->Parse( NULL, false );
      
    }

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