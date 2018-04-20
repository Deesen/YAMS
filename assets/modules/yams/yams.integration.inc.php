<?php

  /*
   * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
   * @copyright Nashi Power (http://nashi.podzone.org/) 2010
   * @license GPL v3
   * @package YAMS (http://modxcms.com/extras/package/?package=543)
   * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 
  This file can be used to help integration of YAMS other
  software with multilingual capabilities, such as Easy 2 Gallery
  
  To use, simply require at the top of the relevant PHP source file
  and a constant called YAMS_INTEGRATION_SETTINGS will be defined
  containing a 'serialized' array of YAMS configuration parameters.
  
  The array must be unserialised before use. So, for example:
  
  require_once "....../assets/modules/yams/yams.integration.inc.php"
  $yamsParams = unserialize(YAMS_INTEGRATION_SETTINGS);
  // an array of language ids for languages which are defined
  // and activated
  $yamsParams['active_lang_ids']
  // an array of language ids for languages which are defined
  // but not activated
  $yamsParams['inactive_lang_ids']
  // an array of language ids for all languages
  $yamsParams['all_lang_ids']
  // the default language id
  $yamsParams['default_lang_id']
  
  If you find this useful and would like other YAMS parameters to be
  added to this constant, please ask via the YAMS forums:
  http://modxcms.com/forums/index.php/board,381.0.html
  */
  
  if ( ! defined('YAMS_INTEGRATION_SETTINGS') )
  {
    require( dirname(__FILE__) . '/class/yams.config.mgr.class.inc.php');
    
    // get an instance of the YAMS singleton class
    $yamsConfigMgr = YamsConfigMgr::GetInstance();
    
    // define the parameters
    define(
      'YAMS_INTEGRATION_SETTINGS'
      , serialize(
          array(
            'active_lang_ids' => $yamsConfigMgr->GetActiveLangIds()
            , 'inactive_lang_ids' => $yamsConfigMgr->GetInactiveLangIds()
            , 'all_lang_ids' => array_merge(
                $yamsConfigMgr->GetActiveLangIds()
                , $yamsConfigMgr->GetInactiveLangIds()
                )
            , 'default_lang_id' => $yamsConfigMgr->GetDefaultLangId()
          )
        )
      );
    // cleanup: unset the yams config manager
    unset($yamsConfigMgr);
  }
    
?>