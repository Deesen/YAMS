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

global $modx;
global $_lang;

if ( ! $modx->IsBackend() )
{
  exit;
}

require_once( dirname( __FILE__ ) . '/class/yams.utils.class.inc.php' );
require_once( dirname( __FILE__ ) . '/class/yams.class.inc.php' );
// require_once( dirname( __FILE__ ) . '/class/yams.module.mgr.class.inc.php' );
// require_once( dirname( __FILE__ ) . '/class/templator.class.inc.php' );
require_once( dirname( __FILE__ ) . '/yams.module.funcs.inc.php' );

// $YMM = YamsModuleMgr::GetInstance();
// echo $YMM->GetOutput();
// return;


//----------------------------------------------
//do post actions here...
//----------------------------------------------
$yams = YAMS::GetInstance();
$errorText = array();
$mode = 'add';
// Keep track of whether it is necessary
// to update the template variables or not
$updateTVs = FALSE;
$updateServerConfig = FALSE;

// Update tv management if necessary
$manageTVs = $yams->GetManageTVs();
if ( isset( $_POST['yams_manage_tvs'] ) )
{
  if ( $_POST['yams_manage_tvs'] == '1' && ! $manageTVs )
  {
    $yams->SetManageTVs( TRUE );
  }
  elseif ( $_POST['yams_manage_tvs'] == '0' && $manageTVs )
  {
    $yams->SetManageTVs( FALSE );
  }
}

if ( isset( $_POST['yams_action'] ) )
{
  $nMatches =
      preg_match(
      '/^(edit_multi|edit_mono|default|submit_multi|submit_mono|submit_templates|submit_other_params|cancel|add|deactivate|activate|delete)(,(\S+))?$/'
      , $_POST['yams_action']
      , $matches
  );
  if ( $nMatches != 1 )
  {
    $mode = 'add';
  }
  else
  {
    switch ( $matches[1] )
    {
      case 'edit_multi':
        $mode = 'edit_multi';
        $edit_lang = $matches[3];
        break;
      case 'edit_mono':
        $mode = 'edit_mono';
        // $edit_lang = $matches[3];
        break;
      case 'default':
        $langId = $matches[3];
        $success = $yams->SetDefaultLangId( $langId, FALSE );
        if ( ! $success )
        {
          $errorText[] =
              'Could not set "'
              . YamsUtils::Escape( $langId )
              . '" as default lang.';
          $yams->Reload();
          break;
        }
        $success = $yams->SaveCurrentSettings();
        if ( ! $success )
        {
          $errorText[] =
              'Unable to save settings to file';
        }
        $mode = 'add';
        break;
      case 'submit_mono':
        if ( ! isset( $_POST['yams_edit_mono_server_name'] ) )
        {
          $mode = 'add';
          break;
        }
        $serverName = $_POST['yams_edit_mono_server_name'];
        $success = $yams->SetMonoServerName( $serverName, FALSE );
        if ( ! $success )
        {
          $errorText[] =
              'Could not set server name to "'
              . YamsUtils::Escape( $serverName );
          $mode = 'add';
          $yams->Reload();
          break;
        }
        $success = $yams->SaveCurrentSettings();
        if ( ! $success )
        {
          $errorText[] =
              'Unable to save settings to file';
          $mode = 'add';
          break;
        }
        $updateServerConfig = TRUE;
        $mode = 'add';
        break;
      case 'submit_multi':
        if (
        !isset( $_POST['yams_edit_tags'] )
            || !isset( $_POST['yams_edit_choose_lang_text'])
            || !isset( $_POST['yams_edit_modx_lang_name'])
            || !isset( $_POST['yams_edit_root_name'])
            || !isset( $_POST['yams_edit_server_name'])
            || !isset( $_POST['yams_edit_is_ltr'])
            || !isset( $_POST['yams_edit_roles_list'])
        )
        {
          $mode = 'add';
          break;
        }
        $allLangIds = array_merge(
            $yams->GetActiveLangIds()
            , $yams->GetInactiveLangIds()
        );
        foreach ( $allLangIds as $whichLangId )
        {
          if ( !isset( $_POST['yams_edit_name_' . $whichLangId ] ) )
          {
            $mode = 'add';
            $errorText[] =
                'Internal error: Name for '
                . YamsUtils::Escape( $whichLangId )
                . '  not set';
            $yams->Reload();
            break 2;
          }
        }
        $tags = $_POST['yams_edit_tags'];
        $chooseLangText = $_POST['yams_edit_choose_lang_text'];
        $modxLangName = $_POST['yams_edit_modx_lang_name'];
        $rootName = $_POST['yams_edit_root_name'];
        $serverName = $_POST['yams_edit_server_name'];
        if ( $_POST['yams_edit_is_ltr'] == '1' )
        {
          $isLTR = TRUE;
        }
        else
        {
          $isLTR = FALSE;
        }
        $rolesList = $_POST['yams_edit_roles_list'];

        $langId = $matches[3];
        foreach ( $allLangIds as $whichLangId )
        {
          $name = $_POST['yams_edit_name_' . $whichLangId ];
          $success = $yams->SetLangName( $langId, $name, $whichLangId, FALSE );
          if ( ! $success )
          {
            $errorText[] =
                'Could not set name to "'
                . YamsUtils::Escape( $name )
                . '" for lang "'
                . YamsUtils::Escape( $whichLangId )
                . '" in lang "'
                . YamsUtils::Escape( $langId )
                . '"';
            $mode = 'add';
            $yams->Reload();
            break 2;
          }
        }
        $success = $yams->SetLangTagsText( $langId, $tags, FALSE );
        if ( ! $success )
        {
          $errorText[] =
              'Could not set tags to "'
              . YamsUtils::Escape( $tags )
              . '" for lang "'
              . YamsUtils::Escape( $langId ) . '"';
          $mode = 'add';
          $yams->Reload();
          break;
        }
        $success = $yams->SetChooseLangText( $langId, $chooseLangText, FALSE );
        if ( ! $success )
        {
          $errorText[] =
              'Could not set choose lang text "'
              . YamsUtils::Escape( $chooseLangText )
              . '" for lang "'
              . YamsUtils::Escape( $langId ) . '"';
          $mode = 'add';
          $yams->Reload();
          break;
        }
        $success = $yams->SetMODxLangName( $langId, $modxLangName, FALSE );
        if ( ! $success )
        {
          $errorText[] =
              'Could not set the MODx language name"'
              . YamsUtils::Escape( $modxLangName )
              . '" for lang "'
              . YamsUtils::Escape( $langId ) . '"';
          $mode = 'add';
          $yams->Reload();
          break;
        }
        $success = $yams->SetRootName( $langId, $rootName, FALSE );
        if ( ! $success )
        {
          $errorText[] =
              'Could not set root name to "'
              . YamsUtils::Escape( $rootName )
              . '" for lang "'
              . YamsUtils::Escape( $langId ) . '"';
          $mode = 'add';
          $yams->Reload();
          break;
        }
        $success = $yams->SetServerName( $langId, $serverName, FALSE );
        if ( ! $success )
        {
          $errorText[] =
              'Could not set server name to "'
              . YamsUtils::Escape( $serverName )
              . '" for lang "'
              . YamsUtils::Escape( $langId ) . '"';
          $mode = 'add';
          $yams->Reload();
          break;
        }
        $success = $yams->SetIsLTR( $langId, $isLTR, FALSE );
        if ( ! $success )
        {
          $errorText[] =
              'Could not set isLTR to "'
              . YamsUtils::Escape( $isLTR )
              . '" for lang "'
              . YamsUtils::Escape( $langId ) . '"';
          $mode = 'add';
          $yams->Reload();
          break;
        }
        $success = $yams->SetRolesAccessList( $langId, $rolesList, FALSE );
        if ( ! $success )
        {
          $errorText[] =
              'Could not set rolesList to "'
              . YamsUtils::Escape( $rolesList )
              . '" for lang "'
              . YamsUtils::Escape( $langId ) . '"';
          $mode = 'add';
          $yams->Reload();
          break;
        }
        $success = $yams->SaveCurrentSettings();
        if ( ! $success )
        {
          $errorText[] =
              'Unable to save settings to file';
          $mode = 'add';
          break;
        }
        $mode = 'add';
        $updateServerConfig = TRUE;
        break;
      case 'submit_templates':
        // Build up the activeTemplates array
        $activeTemplates = array();
        $limit = $yams->GetTemplateInfo( $info );
        for ( $i = 0; $i < $limit; $i++ )
        {
          $row = mysql_fetch_assoc( $info );
          if (
          isset( $_POST['template,' . $row['id']] )
              && $_POST['template,' . $row['id']] == '1'
          )
          {
            $activeTemplates[ $row['id'] ] = NULL;
          }
        }
        // $errorText[] = print_r( $activeTemplates, TRUE );
        $success = $yams->SetActiveTemplates( $activeTemplates );
        if ( !$success )
        {
          $errorText[] =
              'Failed to set active templates: '
              . YamsUtils::Escape(
              print_r( $activeTemplates, TRUE )
          );
          break;
        }
        $updateTVs = TRUE;
        break;
      case 'submit_other_params':
        $success = $yams->SetRedirectionMode( $_POST['yams_redirection_mode'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set redirection.';
        }
        $success = $yams->SetHTTPStatus( $_POST['yams_http_status'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the http status for redirection to the default language.';
        }
        $success = $yams->SetHTTPStatusNotDefault( $_POST['yams_http_status_not_default'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the http status for redirection to non-default languages.';
        }
        $success = $yams->SetHTTPStatusChangeLang( $_POST['yams_http_status_change_lang'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the http status for changing languages.';
        }
        $success = $yams->SetAcceptMODxURLDocIdsString( $_POST['yams_accept_modx_url_doc_ids'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the list of index.php?id= document ids.';
        }
        $success = $yams->SetHideFields( $_POST['yams_hide_fields'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'hide fields\' option.';
        }
        $success = $yams->SetTabifyLangs( $_POST['yams_tabify_langs'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'tabify langs\' option.';
        }
        $success = $yams->SetSynchronisePagetitle( $_POST['yams_synchronise_pagetitle'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'synchronise pagetitle\' option.';
        }
        $success = $yams->SetEasyLingualCompatibility( $_POST['yams_easylingual_compatibility'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'EasyLingual Compatibility\' option.';
        }
        $success = $yams->SetUseMimeDependentSuffixes( $_POST['yams_use_mime_dependent_suffixes'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'use mime dependent suffixes\' option.';
        }
        $success = $yams->SetUseStripAlias( $_POST['yams_use_strip_alias'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'use strip alias\' option.';
        }
        $success = $yams->SetShowSiteStartAlias( $_POST['yams_show_site_start_alias'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'Show site start alias\' option.';
        }
        $success = $yams->SetRewriteContainersAsFolders( $_POST['yams_rewrite_containers_as_folders'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'Rewrite containers as folders\' option.';
        }
        $success = $yams->SetLangQueryParam( $_POST['yams_lang_query_param'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'current language GET parameter\' option.';
        }
        $success = $yams->SetChangeLangQueryParam( $_POST['yams_change_lang_query_param'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'change language GET/POST parameter\' option.';
        }
        $success = $yams->SetMODxSubdirectory( $_POST['yams_modx_subdirectory'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'set MODx subdirectory\' option.';
        }
        $success = $yams->SetURLConversionMode( $_POST['yams_url_conversion_mode'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the URL conversion mode.';
        }
        $oldUseMultilingualAliases = $yams->GetUseMultilingualAliases();
        $success = $yams->SetUseMultilingualAliases( $_POST['yams_use_multilingual_aliases'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'Use multiilingual aliases\' option.';
        }
        else
        {
          $newUseMultilingualAliases = $yams->GetUseMultilingualAliases();
          if ( $oldUseMultilingualAliases != $newUseMultilingualAliases )
          {
            $updateTVs = TRUE;
          }
        }
        $oldMultilingualAliasesAreUnique = $yams->GetMultilingualAliasesAreUnique();
        $success = $yams->SetMultilingualAliasesAreUnique( $_POST['yams_multilingual_aliases_are_unique'] );
        if ( ! $success )
        {
          $errorText[] = 'Could not set the \'Multiilingual aliases are unique\' option.';
        }
        else
        {
          $newMultilingualAliasesAreUnique = $yams->GetMultilingualAliasesAreUnique();
          if ( $oldMultilingualAliasesAreUnique != $newMultilingualAliasesAreUnique )
          {
            $updateTVs = TRUE;
          }
        }
        break;
      case 'add':
        if (
        !isset( $_POST['yams_add_lang'] )
            || !isset( $_POST['yams_add_tags'] )
            || !isset( $_POST['yams_add_choose_lang_text'] )
            || !isset( $_POST['yams_add_modx_lang_name'] )
            || !isset( $_POST['yams_add_root_name'] )
            || !isset( $_POST['yams_add_server_name'] )
            || !isset( $_POST['yams_add_is_ltr'] )
            || !isset( $_POST['yams_add_name_new'] )
        )
        {
          $mode = 'add';
          break;
        }
        $allLangIds = array_merge(
            $yams->GetActiveLangIds()
            , $yams->GetInactiveLangIds()
        );
        foreach ( $allLangIds as $whichLangId )
        {
          if ( !isset( $_POST['yams_add_name_' . $whichLangId ] ) )
          {
            $mode = 'add';
            break 2;
          }
        }
        $langId = $_POST['yams_add_lang'];
        $tags = $_POST['yams_add_tags'];
        $chooseLangText = $_POST['yams_add_choose_lang_text'];
        $modxLangName = $_POST['yams_add_modx_lang_name'];
        $rootName = $_POST['yams_add_root_name'];
        $serverName = $_POST['yams_add_server_name'];
        $isLTR = $_POST['yams_add_is_ltr'];

        $langNames = array();
        foreach ( $allLangIds as $whichLangId )
        {
          $langNames[ $whichLangId ] = $_POST['yams_add_name_' . $whichLangId ];
        }
        $langNames[ $langId ] = $_POST['yams_add_name_new'];

        $success = $yams->AddLang(
            $langId
            , $tags
            , $chooseLangText
            , $modxLangName
            , $rootName
            , $serverName
            , $langNames
            , $isLTR
            , TRUE
        );
        if ( ! $success )
        {
          $errorText[] =
              'Could not add language with lang id "'
              . YamsUtils::Escape( $langId )
              . '"';
          $mode = 'add';
          break;
        }
        $updateTVs = TRUE;
        $mode = 'add';
        break;
      case 'deactivate':
        $langId = $matches[3];
        $success = $yams->DeactivateLangId( $langId );
        if ( ! $success )
        {
          $errorText[] =
              'Could not deactivate lang "'
              . YamsUtils::Escape( $langId )
              . '"';
          $mode = 'add';
          break;
        }
        $updateTVs = TRUE;
        $updateServerConfig = TRUE;
        $mode = 'add';
        break;
      case 'activate':
        $langId = $matches[3];
        $success = $yams->ActivateLangId( $langId );
        if ( ! $success )
        {
          $errorText[] =
              'Could not activate lang "'
              . YamsUtils::Escape( $langId )
              . '"';
          $mode = 'add';
          break;
        }
        $updateTVs = TRUE;
        $updateServerConfig = TRUE;
        $mode = 'add';
        break;
      case 'delete':
        $langId = $matches[3];
        $success = $yams->DeleteLang( $langId );
        if ( ! $success )
        {
          $errorText[] =
              'Could not delete lang "'
              . YamsUtils::Escape( $langId )
              . '"';
          $mode = 'add';
          break;
        }
        $mode = 'add';
        break;
      case 'cancel':
      default:
        $mode = 'add';
    }
  }
}

// Check to see if the URLs of the active langs are unique
//    if ( ! $yams->ActiveURLsAreUnique() )
//    {
//      $errorText[] = 'Warning: The URLs of the active languages are not unique! Please alter the root names or the server names to make them unique.';
//    }

if ( $updateServerConfig )
{
  $errorText[] = 'The .htaccess or server configuration may required updating as a result of the change that has just been made. The current setup should be checked against the Server Config tab.';
}

$activeLangIds = $yams->GetActiveLangIds();
$inactiveLangIds = $yams->GetInactiveLangIds();
$allLangIds = array_merge( $activeLangIds, $inactiveLangIds );
$defaultLangId = $yams->GetDefaultLangId();

if ( $updateTVs )
{
  // Get a list of the current TV names
  YamsGetMODxTVs( $modxTVs );
  // Loop over the template variables
  $tvs = $yams->GetDocVarNames();
//  $tvCaption = array(
//      'pagetitle' => 'Title'
//      , 'longtitle' => 'Long Title'
//      , 'description' => 'Description'
//      , 'introtext' => 'Summary'
//      , 'menutitle' => 'Menu Title'
//      , 'content' => 'Content'
//  );

  // Loop over the template variables
  foreach ( $tvs as $tv )
  {
    // Loop over the active languages
    // create template variables which don't exist
    foreach ( $activeLangIds as $langId )
    {
      // Check if the template variable exists.
      // If not, create it
      $newlyCreatedTV = FALSE;
	    $newlyAssociatedTV = FALSE;
      $multiLangTV = $tv . '_' . $langId;
      if ( ! array_key_exists(
        $multiLangTV
        , $modxTVs
        ) )
      {
        // The template variable doesn't exist

        if ( $yams->GetManageTVs() )
        {
          // Create the template variable
          // and get the template variable id
          $tvType = $yams->GetDocVarType( $tv );
          $tvCaption = $yams->GetDocVarCaption( $tv, $langId );
          $data = array(
              'name' => $modx->db->escape( $multiLangTV )
              , 'caption' => $modx->db->escape( $tvCaption )
              , 'description' => $modx->db->escape( $tv . ' (' . $langId . ')' )
              , 'type' => $modx->db->escape( $tvType )
              , 'elements' => ''
              , 'display' => ''
              , 'rank' => 0
              , 'locked' => 0
          );
          $tblName = $modx->getFullTableName('site_tmplvars');
          $tvId =
              $modx->db->insert(
              $data
              , $tblName
          );
          // Set a flag to say that it is newly created.
          if ( $tvId )
          {
            $newlyCreatedTV = TRUE;
            $errorText[] = 'Created a template variable called "' . $multiLangTV . '" (' . $tvId . ').';
          }
          else
          {
            $errorText[] = 'Error creating a template variable called "' . $multiLangTV . '".';
          }
        }
        else
        {
          // Get the user to create the tvs manually
          $errorText[] = 'Please create a template variable called "' . $multiLangTV . '" and associate it with the active templates.';
        }
      }

      // Now check that the template variables are
      // associated with the correct templates.

      // Each multilingual tv should be associated
      // with each active template

      // Get the id of the template variable in question
      if ( ! $newlyCreatedTV )
      {
        $tvId = $modxTVs[ $multiLangTV ];
      }

      // Get all the templates currently associated with this tv
      YamsGetMODxTemplatesForTV( $tvId, $templates );
      $templateIds = array_keys( $templates );

      // If the tv is not associated with an active template
      // create that association
      $unAssociatedActiveTemplates = array_diff(
          $yams->GetActiveTemplates()
          , $templateIds
      );

      if ( $yams->GetManageTVs() )
      {
        foreach ( $unAssociatedActiveTemplates as $templateId )
        {
          // Associate template variable tvId with template templateId
          $newId = YamsAddAssociationForTV( $tvId, $templateId );
          if ( ! $newId )
          {
            $errorText[] = 'Failed to associate template variable ' . $multiLangTV . '" with template number ' . $templateId . ' .';
          }
          else
          {
            $newlyAssociatedTV = TRUE;
            $errorText[] = 'Associated template variable "' . $multiLangTV . '" with template number ' . $templateId . ' .';
          }
        }
      }
      else
      {
        foreach ( $unAssociatedActiveTemplates as $templateId )
        {
          $errorText[] = 'Template variable "' . $multiLangTV . '" needs to be associated with template number ' . $templateId . ' .';
        }
      }

      // If the tv is associated with an inactive template
      // remove that association
      // If the tv is not associated with an active template
      // create that association
      $associatedInactiveTemplates = array_diff(
          $templateIds
          , $yams->GetActiveTemplates()
      );

      if ( $yams->GetManageTVs() )
      {
        if ( count($associatedInactiveTemplates) > 0 )
        {
          $result = YamsRemoveAssociationsForTV( $tvId, $associatedInactiveTemplates );
          if ( ! $result )
          {
            $errorText[] = 'Error when removing association of "' . $multiLangTV . '" with template(s) ' . implode( ',', $associatedInactiveTemplates ) . ' .';
          }
          else
          {
            $errorText[] = 'Removed association of template variable "' . $multiLangTV . '" with template(s) ' . implode( ',', $associatedInactiveTemplates ) . ' .';
          }
        }
      }
      else
      {
        foreach ( $associatedInactiveTemplates as $templateId )
        {
          $errorText[] = 'Template variable "' . $multiLangTV . '" does not need to be associated with template number ' . $templateId . ' .';
        }
      }

      if ( $yams->GetManageTVs() )
      {
        // Handle the default content for newly created template variables
        if ( $newlyCreatedTV || $newlyAssociatedTV )
        {
          switch ( $tv )
          {
          case 'alias':
            if (! $yams->GetUseMultilingualAliases() )
            {
              break;
            }
            // Create a list of active templates
            $activeTemplates = $yams->GetActiveTemplates();

            if ( count( $activeTemplates ) > 0 )
            {
              // Get a list of all documents that have these templates
              // Loop over the documents.
              $result =
                  $modx->db->select(
                  'id,' . $modx->db->escape( $tv )
                  , $modx->getFullTableName('site_content')
                  , 'template IN ('
                  . implode(',', $activeTemplates)
                  . ') AND deleted=0'
              );
              // For each document copy the content from the
              // document variable to the template variable
              $nDocs = mysql_num_rows( $result );
              for ( $i = 0; $i < $nDocs; $i++ )
              {
                $row = mysql_fetch_assoc( $result );
                $id = $row['id'];
                $tvVal = $row[ $tv ];

                if ( $yams->GetMultilingualAliasesAreUnique() )
                {
                  // Prepend the document alias by the language id
                  // to make it unique - except for the default
                  // language - which can have the same alias
                  // as the document variable...
                  if ( $langId == $yams->GetDefaultLangId() )
                  {
                    $tvVal = $tvVal;
                  }
                  else
                  {
                    $tvVal = $langId . '-' . $tvVal;
                  }
                }

                if ( $tvVal == '' )
                {
                  // The content is empty, so there is nothing to copy
                  continue;
                }

                // Check if there is any existing content.
                // If so, leave it as is...
                $selectResult =
                    $modx->db->select(
                    'value'
                    , $modx->getFullTableName('site_tmplvar_contentvalues')
                    , 'tmplvarid='
                    . $modx->db->escape( $tvId )
                    . ' AND contentid='
                    . $modx->db->escape( $id )
                );
                $selectNRows = mysql_num_rows( $selectResult );
                if ( $selectNRows > 1)
                {
                  // error, there should only be 1 result...
                  continue;
                }
                if ( $selectNRows == 1 )
                {
                  $row = mysql_fetch_assoc( $selectResult );
                  if ( $row['value'] != '' )
                  {
                    // Content is already exists
                    // Leave it as is...
                    continue;
                  }
                  // Copy over the content...
                  $data = array(
                      'value' => $modx->db->escape( $tvVal )
                  );
                  $success =
                      $modx->db->update(
                      $data
                      , $modx->getFullTableName('site_tmplvar_contentvalues')
                      , 'tmplvarid='
                      . $modx->db->escape( $tvId )
                      . ' AND contentid='
                      . $modx->db->escape( $id )
                  );
                  if ( $success )
                  {
                    $errorText[] = 'Copied content from ' . YamsUtils::Escape( $tv ) . ' field to newly created ' . YamsUtils::Escape( $multiLangTV ) . ' for document ' . $id . '.';
                  }
                  else
                  {
                    $errorText[] = 'Failed to copy content from ' . YamsUtils::Escape( $tv ) . ' field to newly created ' . YamsUtils::Escape( $multiLangTV ) . ' for document ' . $id . '.';
                  }
                  continue;
                }
                $data = array(
                    'tmplvarid' => $modx->db->escape( $tvId )
                    , 'contentid' => $modx->db->escape( $id )
                    , 'value' => $modx->db->escape( $tvVal )
                );
                $success =
                    $modx->db->insert(
                    $data
                    , $modx->getFullTableName('site_tmplvar_contentvalues')
                );
                if ( $success )
                {
                  $errorText[] = 'Copied content from ' . YamsUtils::Escape( $tv ) . ' field to newly created ' . YamsUtils::Escape( $multiLangTV ) . ' for document ' . YamsUtils::Escape( $id ) . '.';
                }
                else
                {
                  $errorText[] = 'Failed to copy content from ' . YamsUtils::Escape( $tv ) . ' field to newly created ' . YamsUtils::Escape( $multiLangTV ) . ' for document ' . YamsUtils::Escape( $id ) . '.';
                }
              }
            }
            
            break;
          default:
            // If this is the default lang and this is
            // a newly created template variable,
            // copy content from the document variable field
            // into the template variable for all documents
            // belonging to all active templates.
            if ( $langId != $yams->GetDefaultLangId() )
              {
                break;
              }
            // Create a list of active templates
            $activeTemplates = $yams->GetActiveTemplates();

            if ( count( $activeTemplates ) > 0 )
            {
              // Get a list of all documents that have these templates
              // Loop over the documents.
              $result =
                  $modx->db->select(
                  'id,' . $modx->db->escape( $tv )
                  , $modx->getFullTableName('site_content')
                  , 'template IN ('
                  . implode(',', $activeTemplates)
                  . ') AND deleted=0'
              );
              // For each document copy the content from the
              // document variable to the template variable
              $nDocs = mysql_num_rows( $result );
              for ( $i = 0; $i < $nDocs; $i++ )
              {
                $row = mysql_fetch_assoc( $result );
                $id = $row['id'];
                $tvVal = $row[ $tv ];

                if ( $tvVal == '' )
                {
                  // The content is empty, so there is nothing to copy
                  continue;
                }

                // Check if there is any existing content.
                // If so, leave it as is...
                $selectResult =
                    $modx->db->select(
                    'value'
                    , $modx->getFullTableName('site_tmplvar_contentvalues')
                    , 'tmplvarid='
                    . $modx->db->escape( $tvId )
                    . ' AND contentid='
                    . $modx->db->escape( $id )
                );
                $selectNRows = mysql_num_rows( $selectResult );
                if ( $selectNRows > 1)
                {
                  // error, there should only be 1 result...
                  continue;
                }
                if ( $selectNRows == 1 )
                {
                  $row = mysql_fetch_assoc( $selectResult );
                  if ( $row['value'] != '' )
                  {
                    // Content is already exists
                    // Leave it as is...
                    continue;
                  }
                  // Copy over the content...
                  $data = array(
                      'value' => $modx->db->escape( $tvVal )
                  );
                  $success =
                      $modx->db->update(
                      $data
                      , $modx->getFullTableName('site_tmplvar_contentvalues')
                      , 'tmplvarid='
                      . $modx->db->escape( $tvId )
                      . ' AND contentid='
                      . $modx->db->escape( $id )
                  );
                  if ( $success )
                  {
                    $errorText[] = 'Copied content from ' . $tv . ' field to newly created ' . YamsUtils::Escape( $multiLangTV ) . ' for document ' . $id . '.';
                  }
                  else
                  {
                    $errorText[] = 'Failed to copy content from ' . $tv . ' field to newly created ' . YamsUtils::Escape( $multiLangTV ) . ' for document ' . $id . '.';
                  }
                  continue;
                }
                $data = array(
                    'tmplvarid' => $modx->db->escape( $tvId )
                    , 'contentid' => $modx->db->escape( $id )
                    , 'value' => $modx->db->escape( $tvVal )
                );
                $success =
                    $modx->db->insert(
                    $data
                    , $modx->getFullTableName('site_tmplvar_contentvalues')
                );
                if ( $success )
                {
                  $errorText[] = 'Copied content from ' . $tv . ' field to newly created ' . YamsUtils::Escape( $multiLangTV ) . ' for document ' . $id . '.';
                }
                else
                {
                  $errorText[] = 'Failed to copy content from ' . $tv . ' field to newly created ' . YamsUtils::Escape( $multiLangTV ) . ' for document ' . $id . '.';
                }
              }
            }
          }
        }
      }
    }
  }
}

if ( ! YamsUtils::IsHTTPS() )
{
  $protocol = 'http://';
}
else
{
  $protocol = 'https://';
}

$errorOutput = '';
// Define the placholders
if ( count( $errorText ) > 0 )
{
  foreach ( $errorText as $index => $message )
  {
    $errorText[$index] = YamsUtils::Escape( $message );
  }
  $errorOutput =
    '<p class="warning">'
      . implode('<br />', $errorText )
      . '</p>';
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $modx->config['manager_direction']; ?>" xml:lang="en"> 
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $modx->config['modx_charset']; ?>" />
    <title>YAMS Module Configuration</title>
    <link rel="stylesheet" type="text/css" href="media/style/<?php echo $modx->config['manager_theme']; ?>/style.css" ></link>
    <script type="text/javascript" src="media/script/scriptaculous/prototype.js"></script>
    <script type="text/javascript" src="media/script/scriptaculous/scriptaculous.js"></script>
    <script type="text/javascript" src="media/script/modx.js" ></script>
    <script type="text/javascript" src="media/script/cb2.js" ></script>
    <script type="text/javascript" src="media/script/tabpane.js" ></script>
    <script type="text/javascript" src="media/script/datefunctions.js" ></script>
  </head>
  <body>
    <h1 style="text-align: center;">YAMS: Yet Another Multilingual Solution</h1>
    <form action="<?php echo YamsUtils::Escape( $protocol . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ); ?>" method="post" ><fieldset>
        <div class="sectionHeader">Configuration</div>
        <div class="sectionBody"><?php
        if ( $errorText != '' )
        {
          echo $errorOutput;
        }
        ?>
          <div class="tab-pane" id="yamsPane">
            <script type="text/javascript">
              tpResources = new WebFXTabPane( document.getElementById( 'yamsPane' ) );
            </script>
            <div class="tab-page" id="documentationTabAbout">
              <h2 class="tab">About</h2>
              <script type="text/javsacript">tpResources.addTabPage( document.getElementById( 'documentationTabAbout') );</script>
              <dl>
              <dt><strong>YAMS</strong></dt> <dd>A highly configurable multilingual solution that doesn't require the user to maintain multiple document trees and which allows the user to work with existing document templates.</dd>
              <dt><strong>Version</strong></dt> <dd><?php echo $yams->GetVersion(); ?>. Check for the latest version in the <a href="http://modxcms.com/forums/index.php/topic,36513.msg220349.html" target="_blank">YAMS latest forum post</a>.</dd>
              <dt><strong>Author</strong></dt> <dd><a href="http://modxcms.com/forums/index.php?action=profile;u=12570" target="_blank">PMS</a><br />Original multilingual alias code supplied by <a href="http://modxcms.com/forums/index.php?action=profile;u=21916" target="_blank">mgbowman</a></dd>
              <dt><strong>Documentation</strong></dt> <dd><a target="_blank" href="http://svn.modxcms.com/docs/display/ADDON/YAMS">YAMS documentation</a>, including setup instructions, placeholders, snippet parameters and How To? guides are now maintained on the MODx Confluence wiki. There is also lots of information embedded in the <a href="http://modxcms.com/forums/index.php/board,381.0.html" target="_blank">YAMS forums</a>.</dd>
              <dt><strong>Contribute</strong></dt> <dd>You can help by translating the <a target="_blank" href="http://svn.modxcms.com/docs/display/ADDON/YAMS">YAMS documentation</a> into new languages. To do this you'll need to sign up for a <a target="_blank" href="http://svn.modxcms.com/jira/secure/Dashboard.jspa">MODx JIRA account</a>, complete a <a target="_blank" href="http://modxcms.com/develop/contribute/cla.html">MODx Contributer License Agreement</a> and submit it to MODx requesting permission to edit the MODx Confluence wiki.</dd>
              <dt><strong>Donate</strong></dt> <dd>To support the time spent developing, maintaining and supporting YAMS, please <a href="http://nashi.podzone.org/donate.xhtml" target="_blank">donate</a>. To purchase support on a more formal basis, please <a href="http://nashi.podzone.org/contact.xhtml" target="_blank">contact Nashi Power</a>.</dd>
              <dt><strong>Copyright (and example site)</strong></dt> <dd><a href="http://nashi.podzone.org/" target="_blank">Nashi Power</a> 2009</dd>
              <dt><strong>Licence</strong></dt> <dd>GPL v3</dd>
              <dt><strong>Forums</strong></dt> <dd>Lots of information about YAMS, including a showcase of sites using YAMS, planned developments, known bugs and plenty of user questions and comments can be accessed at the <a href="http://modxcms.com/forums/index.php/board,381.0.html" target="_blank">YAMS board on the MODx Forums</a>.</dd>
              <dt><strong>Description</strong>:</dt>
              <dd><p>YAMS allows users to define language groups and specify certain
              templates as multilingual. All documents associated with those
              templates are then accessible in multiple languages via different URLs.
              The different language versions can be distinguished by root folder
              name, by server name or using a query parameter. Unlike other
              multilingual solutions, it is NOT necessary to manage multiple
              document trees with YAMS. Instead, all content for all languages
              is stored in template variables in the multilingual documents.
              YAMS has a ManagerManager extension that will organise the template
              variables for different languages onto different tabs. YAMS is
              also capable of creating and managing the multilingual template variables
              automatically. Whether or not a template is multilingual or monolingual
              can be configured simply via the module interface.</p>
              <p>Multi-language content for the main document variables
              (pagetitle, longtitle, description, introtext, menutitle, content)
              is handled automatically and transparently and this content is subject to normal
              MODx caching. No special syntax is required for these document
              variables or for internal URLs. For example, use <code>[*content*]</code> and the
              correct language content will appear and use <code>[~id~]</code> and an URL to the correct
              language page will be created.</p>
              <p>In addition, YAMS provides a range of placeholders which provide access
              to the language tag (for use in <code>lang=&quot;&quot;</code> or <code>xml:lang=&quot;&quot;</code>),
              the language direction (for use in <code>dir=&quot;&quot;</code>) and
              language name.  These can be used throughout the site, including in snippet templates.</p>
              <p>More advanced functionality is available via the YAMS snippet call.
              For example, via the snippet call it is possible to repeat content
              over multiple languages using templates. It is also possible to generate
              language lists or drop-down boxes in order to change language.</p>
              <p>Since snippets are generally responsible for parsing the
              placeholders like <code>[+pagetitle+]</code> in templates supplied to them,
              they wont automatically know to insert the correct multilingual
              content. For Ditto this can be overcome this by using an extender. An
              extension is also available for jot and special templates are available for Wayfinder.
              For the templates of other snippets it is possible to replace the
              placeholders by special YAMS snippet calls, eg:
                <br />
                <code>[[YAMS? &amp;get=`data` &amp;from=`pagetitle` &amp;docid=`[+id+]`]]</code><br />
              YAMS compatible default templates for Wayfinder already include the appropriate YAMS snippet calls.</p>
              <p>As of version 1.0.3, YAMS *should be* fully compatible with <a href="http://modxcms.com/forums/index.php/topic,32807.0.html" target="_blank">EasyLingual</a>. See the Setup tab for instructions on how to migrate a site from EasyLingual to YAMS.</p>
              <p>YAMS has been developed on MODx Evolution v0.9.6.3 and with the latest version of PHP (5.2.6-3). YAMS will not work with sites running PHP 4.</p></dd>
              <dt><strong>Credits</strong></dt> <dd><p>The language icons used in the
              language select drop down are from <a href="http://www.languageicon.org/" target="_blank">Language Icon</a>,
              released under the <a href="http://creativecommons.org/licenses/by-sa/3.0/">Attribution-Share Alike 3.0 Unported</a> license.
              Thanks to @MadeMyDay for having the courage to be one of the first guinea pigs!</p>
              </dd>
              </dl>
              <p><strong>New in this version</strong>:</p>
<ul>
<li><strong>Bug Fix</strong>: Fixed a bug whereby YAMS would revert to the default language instead
  of staying on the current language when using the 'MODx URLs' configuration
  parameter (set to '*', for example)</li>
<li><strong>Bug Fix</strong>: Fixed bug with incorrect (over-zealous) use of urldecode and urlencode.
  $_GET and $_POST are automatically url-decoded by PHP, whereas
  $_SERVER['REQUEST_URI'] is not.</li>
</ul>
              <p><strong>New in version 1.2.0 RC1</strong>:</p>
<ul>
<li>Added a file called yams.integration.inc.php that defines a constant
  containing YAMS configuration parameters that can be used to help
  integration of YAMS with other multilingual software.</li>
<li><strong>Bug Fix</strong>: Added protection against errors if the YAMS config file is accessed directly.
</li>
<li><strong>Bug Fix</strong>: Fixed a problem whereby nested YAMS placeholders were not getting evaluated.</li>
<li>Updated the readme.txt.</li>
<li>Added three new placeholders (yams_multi), (yams_mono) and (yams_type)
  in order to help filter documents by type.</li>
<li>Moved the documentation of language settings and configuration options from the
  module interface to the online documentation.</li>
<li>Major code tidy up and reorganisation:
  <ul>
    <li>Utility methods moved into the a new YamsUtils class (yams.utils.class.inc.php).</li>
    <li>Some methods have been updated for improved security, by including checks for
    bad UTF-8 byte streams and by stripping control codes where appropriate.</li>
    <li>All config file manipulation methods (getters, setters, etc..) have been moved
    into a YamsConfigMgrAbstract abstract class (yams.config.mgr.class.inc.php),
    which the main YAMS class now inherets. This in turn inherits from a singleton
    abstract class.</li>
    <li><strong>Bug Fix</strong>: The escaping that YAMS was doing when writing the PHP config file was not entirely
    correct and this could have been taken advantage of by someone with malicious
    intent that managed to obtain access to the YAMS Module interface. This has
    now been fixed.</li>
    </ul>
</li>
</ul>
              <p><strong>New in version 1.1.9</strong>:</p>
<ul>
<li><strong>Bug Fix</strong>: Fixed a bug introduced at version 1.1.8, which breaks ((yams_data:..))</li>
<li><strong>Bug Fix</strong>: Applied <a href="http://modxcms.com/forums/index.php/topic,43821.0.html">kongo09's patch</a>, which fixes a bug whereby default content is not copied over to new fields when multilingual tvs are associated with new templates.</li>
</ul>
              <p><strong>New in version 1.1.8</strong>:</p>
<ul>
<li>Updated YAMS ManagerManager rules so that when hide fields is on, multilingual aliases are hidden when multilingual aliases are switched off and the standard document alias is hidden when multilingual aliases are switched on.
</li><li>Updated the documentation for Hide Fields accordingly.
</li><li>Bug Fix: Fixed a &lt;p&gt; that should have been a &lt;/p&gt; in the module
</li><li>Updated the forum link to http://modxcms.com/forums/index.php/board,381.0.html
</li><li>Added a title field to the YAMS ditto extender. This outputs the page title.
</li><li>Bug Fix: Corrected a typo str2lower -> strtolower.<br />
  This bug fix is necessary for YAMS to work over HTTPS.<br />
  Reported by noes: http://modxcms.com/forums/index.php/topic,42752.0.html
</li><li>Added an additional check to prevent crashing if $modx->documentObject doesn't exist.
</li><li>Made the Expand function public.
</li><li>Bug Fix: Fixed a bug whereby the current language would be lost when changing page using ditto pagination and unique multilingual aliases.
</li><li>Bug Fix: Corrected a problem with switching languages when using unique multilingual aliases.
</li><li>Improved installation instructions.
</li><li>Bug Fix: Fixed a bug whereby YAMS ManagerManager rules would be applied to all (rather than no) templates when no multilingual templates are specified.
</li><li>Documentation updates</li>
</ul>
              <p><strong>New in version 1.1.7 alpha RC7</strong>:</p>
<ul>
<li>Included <a href="http://modxcms.com/forums/index.php?action=profile;u=16271" target="_blank">@French Fries</a>' Wayfinder breadcrumbs templates and updated the How To?
documentation.
</li><li>Included an option to turn off automatic redirection to the correct multilingual
URL when standard MODx style URLs are encountered for specified document ids.
</li><li><strong>Bug fix</strong>: MODx default behaviour is that when a document not assigned any alias
it is instead referred to by its id. This wasn't implemented. Done now, except
that for multilingual documents with unique aliases on, the documents are
referred to by <code><em>langId</em>-<em>docId</em></code>.
</li><li>Removed a system event log command that was added for debugging purposes but
accidentally left in the code.</li>
</ul>
              <p><strong>New in version 1.1.7 alpha RC6</strong>:</p>
<ul>
<li>Most of the languageicons have been removed from the distribution. The full
set can be downloaded from <a href="http://www.languageicon.org/" target="_blank">http://www.languageicon.org/</a>.</li>
<li>Removed the 'do you want to allow YAMS to manager multilingual variables' option
from the multilingual templates tab.
</li><li>Tweaks to the module interface to make it easier to to find submit buttons.
</li><li>Removed some unneeded checks in frequently executed code for efficiency
</li><li><strong>Bug fix</strong>: Fixed a couple of errors whereby YAMS was trying to access a regexp match
that was undefined (rather than empty)
</li><li><strong>Bug fix</strong>: Fixed an error that could potentially result in YAMS not correctly
identifying an HTTPS connection.
</li><li><p>Efficiency improvements.</p>
<ul>
<li>YAMS removes empty constructs instead of processing them.</li>
<li>When loading monolingual documents, now only the default language variant is
 parsed. Previously all language variants were parsed but only one was served.
 Monolingual documents will be served approx 1/n times faster, where n is the
 number of languages.</li>
<li>As soon as a document is loaded from cache YAMS now strips out superfluous
 language variants. Previously it evaluated all language variants but served
 just one. For documents with a lot of uncachable content this can lead to an
 improvement in speed of approx 1/n, where n is the number of languages.</li>
</ul>     
</li><li><strong>Bug fix</strong>: Updated the managermanager rules to fix a bug whereby if more than one
custom tv was assigned to a language, only the last would be tabified.</li>
</ul>
              <p><strong>New in version 1.1.7 alpha RC5</strong>:</p>
<ul>
<li><strong>Bug fix</strong>: Fixed a bug whereby on first save of a newly created multilingual
document, pagetitles and aliases would not get updated.
</li><li><strong>Bug fix</strong>: Fixed an URL encoding issue. The php header function accepts
a plain text URL, but YAMS was passing it an HTML encoded URL.
</li><li><strong>Bug fix</strong>: The new multilingual URL capability had broken query parameter mode
and non-friendly URLs. Fixed.</li>
</ul>
              <p><strong>New in version 1.1.7 alpha RC4</strong>:</p>
<ul>
<li>Now, if YAMS doesn't recognise an URL as being valid, but MODx does, then YAMS
will redirect from the MODx recognised URL to the correct URL using the status
codes defined on the 'Configuration' tab, rather than generating 404 not found.
(This aids compatibility with existing MODx resources that don't understand
multilingual URLs and is what YAMS used to do in previous versions before I broke
it!)</li>
</ul>
              <p><strong>New in version 1.1.7 alpha RC3</strong>:</p>
<ul>
<li><strong>Bug fix</strong>: Corrected an <a href="http://modxcms.com/forums/index.php/topic,36513.msg243901.html#msg243901" target="_blank">.htaccess bug</a>.</li>
</ul>
              <p><strong>New in version 1.1.7 alpha RC2</strong>:</p>
<ul>
<li><strong>Bug fix</strong>: Corrected a small URL encoding bug.</li>
<li>Included an option to make MODx stripAlias optional for multilingual aliases.</li>
<li>YAMS now does automatic updating of aliases, checking for unique aliases and
checking for duplicate aliases.</li>
<li>Updated the YAMS managermanager rules so that they work with the latest version
of managermanager (0.3.4), which refers to tvs by name instead of id like MODx.
YAMS should be backwards compatible with older versions of both mm and MODx.
</li><li><strong>Bug fix</strong>: Corrected a dodgy regexp which was causing URL resolution problems
when installing into a subdirectory.
</li><li>Updated the friendly URL config to include standard MODx stuff (avoids
confusion about whether it should be there or not)
</li><li>Updated the root name trailing slash redirection to be consistent with apache
guidelines.
</li><li>stripAlias is now implemented for multilingual URLs. Need to check that it
works on pre-Evo installs.
</li><li>stripAlias can result in empty aliases. Need to handle that.
</li><li>Implemented automatic pagetitle update
</li><li>Implemented better document pagetitle synchronisation
</li><li>Started implementing automatic alias updating.
</li><li><strong>Bug fix</strong>: YAMS could return HTTP OK for monolingual documents with an extra
root name prefix. Fixed. Now permanent redirects to correct monolingual URL.
</li><li>Implemented mime dependent aliases. Currently not possible to set the
mime-alias map via the module interface.
</li><li>Modified the YAMS to encode tv names in the same way that MODx does for
0.9.6.3 and earlier versions. (Previously the encoding was not done in
completely the same way.)
</li><li>Altered the PreParse method to prevent the recursion limit from being reached
on complicated documents. It now returns a flag that says whether it needs to
be called again.
</li><li>Tidied up the comments in the code a bit.
</li><li><strong>Bug fix</strong>: Corrected a missing variable declaration in yams.module.inc.php</li>
</ul>
              <p><strong>New in version 1.1.7 alpha RC1</strong>:</p>
<ul>
<li><strong>Bug Fix</strong>: Corrected (I hope) and URL bug which would affect documents nested
at level 2 and greater when using friendly alias paths.
</li>
</ul>
              <p><strong>New in version 1.1.6 alpha</strong>:</p>
              <ul>
<li>Added SEO Strict style URL functionality. YAMS will now permanent redirect
to the correct URL when:<br />
* slashes are incorrectly specified (multiple slashes or missing trailing slash)<br />
* the prefix and suffix of a filename are missed<br />
* the prefix and suffix are included for a folder<br />
In addition, there is now a new option that allows the re-writing of containers
as folders:<br />
<code>.../mycontainer.html code</code> -> <code>.../mycontainer/</code><br />
Currently there is no facility for overriding this on a document by document
basis.
</li><li>Introduced a new URL redirection mode: "current else browser". When redirecting to
a multilingual page, if the site has been visited previously and a language
cookie has been saved, the visitor will be redirected to a page in that language.
Otherwise they will be redirected to a page based on their browser settings.</li>
</ul>
              <p><strong>New in version 1.1.6 alpha RC1</strong>:</p>
<ul>
<li><strong>Bug Fix</strong>: Fixed a problem whereby documents could break when loading them from
the cache.
</li><li><strong>Bug Fix</strong>: Fixed a server config bug affecting rootname to rootname/ redirection.
</li><li><strong>Bug Fix</strong>: Repaired a missing space in a mysql query.
</li>
</ul>
              <p><strong>New in version 1.1.5 alpha</strong>:</p>
<ul>
<li><strong>Bug Fix</strong>: Fixed a parse bug which could occasionally involve a regular
expression grabbing too much and breaking YAMS constructs.
</li>
</ul>
              <p><strong>New in version 1.1.5 alpha RC 3</strong>:</p>
<ul>
<li><strong>Bug Fix</strong>: Corrected a bug in breadcrumbs.101.yams.snippet.php
</li><li>Made use of the new ((yams_data:...)) construct to optimise
breadcrumbs.101.yams.snippet.php by minimising database queries.
</li><li>Renamed breadcrumbs.101.yams.snippet.php to give it the php extension.
Also included code to protect it against direct execution.
</li><li>PreParseOptimise is fairly resource intensive, so only call it if
pre-parse-optimisation is really required (that is, if there is more than one
nested yams-select construct.)
</li><li>yams_doc and yams_docr were really inefficient, because each document alias
was requiring at least one database query. Now use a cache which stores the alias
of each document in each language, as well as its parent. This can bring result
in major performance enhancements
</li><li>Updated the server config to include redirection from mysite.com/root to
mysite.com/root/
</li><li><strong>Bug Fix</strong>: Made sure yams-in blocks get parsed on the
final PostParse call. As a
result of this fix Ditto will no longer complain that it can't find the language
file when using <code>&amp;language=`(yams_mname)`</code>.
</li><li>It is not necessary for PostParse to be called recursively. Fixed.
</li><li>YAMS was executing on the OnWebPageComplete event... but this was completely
unnecessary. Fixed.
</li><li><strong>Bug Fix</strong>: Fixed misplaced PostParse argument.
</li><li><strong>Bug Fix</strong>: Corrected a bug which would cause the current language block of a
repeat construct to be output as blank when no currentTpl was specified. (In
this case, the repeatTpl should be used.)
</li><li>Updated the StoreYamsInCallback and StoreYamsRepeatCallback to use a new
YamsCounter number rather than using the number of the block being cached.
</li>
</ul>
              <p><strong>New in version 1.1.5 alpha RC 2</strong>:</p>
<ul>
<li><strong>Bug Fix</strong>: Corrected another URL encoding bug that would prevent incorrect
changing of language and which sometimes gave rise to blank pages.</li>
</ul>
              <p><strong>New in version 1.1.5 alpha RC 1</strong>:</p>
<ul>
<li><strong>Bug Fix</strong>: Corrected an URL encoding bug that would prevent incorrect changing
of language.
</li><li>Updated the Wayfinder and Ditto extensions to use <code>[[YAMS? &amp;get=`data`</code>.
</li><li>Updated the manager manager rules to ensure that the template variables are
moved to tabs in the correct order. Wasn't sure if the existing array_merge
was simply concatenating the sorted arrays.
</li><li>Updated the YAMS snippet cal to make use of the <code>((yams_data:...</code> syntax.
<code>[[YAMS? &amp;get=`content`</code> is now depracated and <code>[[YAMS? &amp;get=`data`</code> should be
used in its place
</li><li>First implementation of the <code>((yams_data:docId:tvname:phx))</code> syntax for improved
performace through minimisation of the number of sql queries. Does not
support PHx yet. Loads data from a maximum of YAMS_DOC_LIMIT documents at time.
</li><li><strong>Bug Fix</strong>: Fixed several bugs introduced when updating the parsing. YAMS
placeholders can now go almost anywhere.
</li><li><p>Fairly major changes to parsing:</p>
<ul>
<li>YAMS now ensures that all chunks and tvs - which may contain YAMS placholders
- are parsed on the PreParse step before handing over to MODx.</li>
<li>It should now be possible to include yams placeholders in cacheable AND
uncacheable snippet calls and in chunk and tv names...
</li>
</ul>
</li><li>Updated documentation to describe multilingual alias modes in more detail.
</li><li>Modified to allow the monolingual URL and (one of) the multilingual URLs
to coincide. Now, when unique multilingual aliases are not being used it
is only necessary for the multilingual language variants to be unique.
</li><li><strong>Bug Fix</strong>: Fixed a bug whereby tv's would be incorrectly sorted in the document
view.
</li><li>Included a How TO? for custom multilingual tvs/chunks/snippets.
</li><li>Updated the multilingual URL generation and checking so as to exclude deleted
documents.
</li><li>Bug Fix: Corrected a bug whereby YAMS would not change language when on the
site start document.</li>
</ul>
              
              <p><strong>New in version 1.1.4 alpha</strong>:</p>
              <ul>
<li><b>Bug Fix</b>: Corrected the Wayfinder How To? module documentation.
</li><li>Implemented automatic redirection of weblinks. This wasn't implemented before.
This works with multilingual weblinks too. In the content fields of a
multilingual weblink it is possible to specify the same or different URLs or docIds
for each language. When using a docId, the target document will be displayed
in the same language as the source document, or the default language if the
final document is monolingual.
</li><li>Made the <code>$yams->ConstructURL( $langId, $docId )</code> method public so that it can
be used as a replacement for <code>$modx->makeUrl( $docId )</code> when YAMSifying existing
snippets etc.
</li><li><b>Bug Fix</b>: Correct a bug in the implementation of friendly url suffixes and prefixes.
This bug made the suffixes and prefixes active at every level instead of just
the filename.
</li><li><b>Bug Fix</b>: Updated the server config. It now displays the correct output when
unique multilingual aliases only are being used. It also advises on virtual
host configuration when server name mode is being used.
</li><li>Corrected a potential bug whereby the second argument of preg_quote was not
specified.
</li><li>Reorganised the params on the 'Configuration' tab and updated the multilingual
alias text a bit.</li>
              </ul>
              <p><strong>New in version 1.1.3 alpha</strong>:</p>
              <ul>
<li>Added support for friendly alias prefixes and friendly alias suffixes.
</li><li><strong>Bug Fix</strong>: Corrected server config bug. ${...} should have been %{...}
</li><li>Added support for phx modifiers on multilingual document variables. The
following are examples of accepted syntax:<br />
<code>[*#content:lcase*]</code><br />
<code>[*introtext:limit=`50`*]</code>
</li><li>Replaced YAMS' own recursive chunk parsing call an iterative call to
MODx's mergeChunkContent. Seemed silly not to reuse existing code.
</li><li>Now YAMS maintains a list of monolingual document ids, to avoid having to
look up whether a document is multilingual in the database each time.
</li><li><strong>Bug Fix</strong>: Fixed a bug in the server config. Was using the output query param
separator instead of the input one. As a result was getting double encoded
ampersands.
</li><li>Modified the default output query separator (used when it is not defined by
PHP) to be &amp;amp; rather than &amp;.
</li><li><strong>Bug Fix</strong>: Fixed problem whereby invalid monolingual URLs which are invalid due
to a multilingual servername being used would redirected to themselves.
</li><li><strong>Bug Fix</strong>: Fixed a bug whereby the alias of the site-start document was being
included in the URL when using friendly alias paths and when it shouldn't have
been because of a YAMS setting.
</li><li><strong>Sort of Bug Fix</strong>: I have removed the mb_strtolower function from the URL
comparison since mbstring library is not active by default in PHP. Was going
to replace it by strtolower - which would have been safe on the encoded URL.
However, since MODx does not support case insensitive URLs anyway - so I have
removed it. True support for case insensitive URLs would be possible but would
require a bit more thought.
</li><li><strong>Bug Fix</strong>: Fixed bug active when friendly alias paths is on which was causing
docs lower than the root to not be found.
</li>
              </ul>
              <p><strong>New in version 1.1.2 alpha</strong>:</p>
              <ul>
<li>Now possible to view document using index.php?id=... So, preview from the document
tree now works again.</li>
<li>Fixed a bug wherby callbacks were being registered statically when they shouldn't
have been.</li>
              </ul>
              <p><strong>New in version 1.1.1 alpha</strong>:</p>
              <ul>
<li>Modified the default multilingual URLs generated by YAMS so that the alias
of the default language variant is the same as that of the document variable.</li>
<li>Implemented a 'Unique Multilingual Aliases' mode. This mode is activated if
unique multilingual aliases are being used. In that case it is not
necessary to specify root names or server names. YAMS can determine the language
group id and document id directly from the alias. The documentation needs
updating now.</li>
<li>Improved commenting of the code a little.</li>
<li>Applied proper URL encoding to the subdirectory and root name.</li>
              </ul>
              <p><strong>New in version 1.1.0 alpha</strong>:</p>
              <ul>
<li>Generalised generated query strings to use the php defined argument separator</li>
<li>Added a parameter for specifying whether aliases are unique or not.</li>
<li>Updated the copying over of default content for multilingual aliases.</li>
<li>Now does proper encoding of URLs. Multibyte URLs are correctly encoded.</li>
<li>Added correct conversion from native encoding to utf-8 to rawurlencoded and
back again for URLs and query parameters.</li>
<li>Added methods for escaping and cleaning strings for embedding in (x)html/xml
documents and updated all occurrences of htmlspecialchars to use them.</li>
<li>Arranged it so that the current language cookie is only set if a valid document
is found and it is multilingual.</li>
<li>If a multilingual alias has not been specified, then nothing is output for the
URL.</li>
<li><p>Incorporated mbowman's YAMS_UX code into YAMS.</p>
<ul>
<li>Generalised it to function with and without friendly alias paths.</li>
<li>Generalised it to function with or without multilingual alias mode</li>
<li>Generalised it to take into account absent filename for site start (only
default language if using multilingual aliases).</li>
<li>Fixed incorrect langId specification.</li>
</ul></li>
<li>YAMS now manages the alias document variable associated with multilingual
aliases.</li>
<li>Default descriptions for Multilingual TVs created by YAMS are now in the correct
language.</li>
<li>YAMS now manages a list of supported document variable types.</li>
<li>Allowed (*.)localhost as a valid server name</li>
              </ul>
            </div>


            <div class="tab-page" id="tabLanguages">
              <h2 class="tab">Language Settings</h2>
              <script type="text/javsacript">tpResources.addTabPage( document.getElementById( 'tabLanguages') );</script><ul><?php
if ( $yams->GetUseLanguageDependentServerNames() )
{ ?>
                <li>Language dependent server name mode is currently <strong>ON</strong>. To disable this mode it is necessary to clear at least one of the monolingual or multilingual server names.</li>
<?php
}
                      else
{ ?>
                <li>Language dependent server name mode is currently <strong>OFF</strong>. To enable this mode it is necessary to specify a monolingual server name and a multilingual server name for each active language.</li>
<?php  
}
if ( $yams->GetUseLanguageDependentRootNames() )
{ ?>
                <li>Language dependent root name mode is currently <strong>ON</strong>. To disable this mode it is necessary to clear all root names.</li>
<?php
}
else
{ ?>
                <li>Language dependent root name mode is currently <strong>OFF</strong>. To enable this mode it is necessary to specify at least one root name.</li>
<?php  
}
if ( $yams->GetUseUniqueMultilingualAliases() )
{ ?>
                <li>Unique multilingual aliases mode is currently <strong>ON</strong>. YAMS can determine the document id and language from the alias alone, so it is not necessary to specify root names or server names.</li>
<?php
}
                      else
{ ?>
                <li>Unique multilingual aliases mode is currently <strong>OFF</strong>. It is not possible to determine the document id and language from the alias alone. Multilingual aliases can be turned on using the settings on the 'Configuration' tab.</li>
<?php
}
if ( $yams->GetUseLanguageQueryParam() )
{ ?>
                <li>Query param mode is currently <strong>ON</strong>. Since the language cannot be uniquely determined from the server and root names, it is necessary to append a language identifying query param to the end of each URL.</li>
<?php
}
                      else
{ ?>
                <li>Query param mode is currently <strong>OFF</strong>. The language can be determined from the server and root names alone. No additional query param is required.</li>
<?php
}
?>
              </ul>
              <table class="grid" summary="Monolingual Settings">
                <caption>Monolingual Settings</caption>
                <thead>
                <?php
                switch ( $mode )
                {
                  case 'edit_mono':
                    ?>
                  <tr>
                    <th class="gridHeader" rowspan="2">Name + DocLink</th>
                    <th class="gridHeader">
                      <button name="yams_action" type="submit" value="submit_mono">Submit</button>
                    </th>
                  </tr>
                  <tr>
                    <th class="gridHeader">
                      <button name="yams_action" type="submit" value="cancel">Cancel</button>
                    </th>
                  </tr>
                  <?php
                  break;
                default:
                  ?>
                  <tr>
                    <th class="gridHeader">Name + DocLink</th>
                    <th class="gridHeader">
                      <button name="yams_action" type="submit" value="edit_mono">Edit</button>
                    </th>
                  </tr>
                <?php
                }
                ?>
                </thead>
                <tbody>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass;?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-MonoServerName" target="_blank">Server Name</a></th>
                    <td class="<?php echo $rowClass;?>"><?php
                  $name = $yams->GetServerName( NULL );
                  switch ( $mode )
                  {
                    case 'edit_mono':
                      ?><input name="yams_edit_mono_server_name" type="text" value="<?php echo $name; ?>"/><?php
                      break;
                    case 'add':
                    default:
                      if ( $name == '' )
                      {
                        $name = '(' . $yams->GetActiveServerName( NULL ) . ')';
                      }
                      echo YamsUtils::Escape( $name );
                  }
                  ?></td>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass;?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-MonoSiteURL" target="_blank">Site URL</a></th>
                    <td class="<?php echo $rowClass;?>"><?php
                      $siteURL = $yams->GetSiteURL( NULL );
                      echo YamsUtils::Escape( $siteURL );
                    ?></td>
                  </tr>
                </tbody>
              </table>
              <table class="grid" summary="Multilingual Settings">
                <caption>Multilingual Settings</caption>
                <thead><?php include 'yams.module.language.settings.header.inc.php'; ?></thead>
                <tfoot><?php include 'yams.module.language.settings.header.inc.php'; ?></tfoot>
                <tbody>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-LangID" target="_blank">Lang ID</a></th><?php
                      foreach ( $allLangIds as $langId )
                      {
                        ?><td class="<?php echo $rowClass; ?>"><?php echo YamsUtils::Escape( $langId );?></td><?php
                      }
                      if ( $mode == 'add' )
                      {
                        ?><td class="<?php echo $rowClass; ?>"><input name="yams_add_lang" type="text" /></td><?php
                      }
                      ?>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-Tags" target="_blank">Tags</a></th><?php
                    foreach ( $allLangIds as $langId )
                    {
                      ?><td class="<?php echo $rowClass; ?>"><?php
                      $langTagsText = $yams->GetLangTagsText( $langId );
                      if ( $langTagsText == '' )
                      {
                        $langTagsText = $langId;
                        }
                        switch ( $mode )
                        {
                          case 'edit_multi':
                            if ( $langId == $edit_lang )
                            {
                              ?><input name="yams_edit_tags" type="text" value="<?php echo $langTagsText; ?>"/><?php
                            }
                            else
                            {
                              echo YamsUtils::Escape( $langTagsText );
                            }
                          break;
                        case 'add':
                        default:
                          echo YamsUtils::Escape( $langTagsText );
                        }
                        ?></td><?php
                      }
                      if ( $mode == 'add' )
                      {
                        ?><td class="<?php echo $rowClass; ?>"><input name="yams_add_tags" type="text" /></td><?php
                      }
                      ?>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-ServerName" target="_blank">Server Name</a></th><?php
                    foreach ( $allLangIds as $langId )
                    {
                      $name = $yams->GetServerName( $langId );
                      ?><td class="<?php echo $rowClass; ?>"><?php
                      switch ( $mode )
                      {
                        case 'edit_multi':
                          if ( $langId == $edit_lang )
                          {
                            ?><input name="yams_edit_server_name" type="text" value="<?php echo $name; ?>"/><?php
                          }
                          else
                          {
                            if ( $name == '' )
                            {
                              $name = '(' . $yams->GetActiveServerName( $langId ) . ')';
                            }
                            echo YamsUtils::Escape( $name );
                          }
                            break;
                          case 'add':
                          default:
                            if ( $name == '' )
                            {
                              $name = '(' . $yams->GetActiveServerName( $langId ) . ')';
                            }
                            echo YamsUtils::Escape( $name );
                        }
                        ?></td><?php
                      }
                      if ( $mode == 'add' )
                      {
                        ?><td class="<?php echo $rowClass; ?>"><input name="yams_add_server_name" type="text" /></td><?php
                      }
                      ?>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-RootName" target="_blank">Root Name</a></th><?php
                      foreach ( $allLangIds as $langId )
                      {
                        $name = $yams->GetRootName( $langId, FALSE );
                        ?><td class="<?php echo $rowClass; ?>"><?php
                        switch ( $mode )
                        {
                        case 'edit_multi':
                          if ( $langId == $edit_lang )
                          {
                            ?><input name="yams_edit_root_name" type="text" value="<?php echo $name; ?>"/><?php
                          }
                          else
                          {
                            echo YamsUtils::Escape( $name );
                            }
                            break;
                          case 'add':
                          default:
                            echo YamsUtils::Escape( $name );
                        }
                        ?></td><?php
                      }
                      if ( $mode == 'add' )
                      {
                        ?><td class="<?php echo $rowClass; ?>"><input name="yams_add_root_name" type="text" /></td><?php
                      }
                      ?>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-SiteURL" target="_blank">Site URL</a></th><?php
                  foreach ( $allLangIds as $langId )
                  {
                    $siteURL = $yams->GetSiteURL( $langId );
                    ?><td class="<?php echo $rowClass; ?>"><?php echo YamsUtils::Escape( $siteURL );?></td><?php
                  }
                  if ( $mode == 'add' )
                  {
                    ?><td class="<?php echo $rowClass; ?>"></td><?php
                  }
                    ?>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-LanguageDirection" target="_blank">Language Direction</a></th><?php
                    foreach ( $allLangIds as $langId )
                    {
                      $langDir = $yams->GetLangDir( $langId );
                      $isLTR = $yams->GetIsLTR( $langId );
                      $ltrSelected = '';
                                        $rtlSelected = '';
                      if ( $isLTR )
                      {
                        $ltrSelected = 'selected="selected"';
                      }
                      else
                      {
                        $rtlSelected = 'selected="selected"';
                      }
                        ?><td class="<?php echo $rowClass; ?>"><?php
                        switch ( $mode )
                        {
                          case 'edit_multi':
                            if ( $langId == $edit_lang )
                            {
                              ?><select name="yams_edit_is_ltr">
                        <option <?php echo $ltrSelected; ?> value="1">ltr</option>
                        <option <?php echo $rtlSelected; ?> value="0">rtl</option>
                      </select><?php
                            }
                            else
                            {
                              echo YamsUtils::Escape( $langDir );
                            }
                            break;
                          case 'add':
                          default:
                            echo YamsUtils::Escape( $langDir );
                        }
                        ?></td><?php
                      }
                      if ( $mode == 'add' )
                      {
                        ?><td class="<?php echo $rowClass; ?>">
                      <select name="yams_add_is_ltr">
                        <option selected="selected" value="1">ltr</option>
                        <option value="0">rtl</option>
                      </select>
                    </td><?php
                    }
                    ?>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-Roles" target="_blank">Roles</a></th><?php
                      foreach ( $allLangIds as $langId )
                      {
                        $rolesAccessList = $yams->GetRolesAccessList( $langId );
                        ?><td class="<?php echo $rowClass; ?>"><?php
                        switch ( $mode )
                        {
                        case 'edit_multi':
                          if ( $langId == $edit_lang )
                          {
                            ?><input name="yams_edit_roles_list" type="text" value="<?php echo $rolesAccessList; ?>"/><?php
                          }
                          else
                          {
                            echo $rolesAccessList;
                          }
                          break;
                        case 'add':
                        default:
                          echo $rolesAccessList;
                        }
                        ?></td><?php
                      }
                      if ( $mode == 'add' )
                      {
                        ?><td class="<?php echo $rowClass; ?>"><input name="yams_add_roles_list" type="text" /></td><?php
                      }
                      ?>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-MODxLanguageName" target="_blank">MODx Language Name</a></th><?php
                    // Get the listing of all MODx langs...
                    $modxLangFiles = scandir(
                        $modx->config['base_path']
                        . 'manager/includes/lang/'
                      );
                    $modxLangArray = array( '' => '(none)');
                    $encodingModifier = $yams->GetEncodingModifier();
                    foreach ( $modxLangFiles as $id => &$name )
                    {
                      if ( preg_match(
                          '/^([a-zA-Z0-9-]+)\.inc\.php$/DU'
                          . $encodingModifier
                          , $name
                          , $matches
                          ) != 1 )
                        {
                        continue;
                      }
                      $modxLangName = $matches[1];
                      $modxLangArray[ $modxLangName ] = $modxLangName;
                    }
                    foreach ( $allLangIds as $langId )
                    {
                      $modxLangName = $yams->GetMODxLangName( $langId );
                      ?><td class="<?php echo $rowClass; ?>"><?php
                      switch ( $mode )
                      {
                        case 'edit_multi':
                          if ( $langId == $edit_lang )
                          {
                            ?><!--<input name="yams_edit_modx_lang_name" type="text" value="<?php echo YamsUtils::Escape( $modxLangName ); ?>"/>--><select name="yams_edit_modx_lang_name"><?php
                            foreach ( $modxLangArray as $value => $content )
                            {
                              if ( $value == $modxLangName )
                              {
                                ?><option selected="selected" value="<?php echo YamsUtils::Escape( $value ); ?>"><?php echo YamsUtils::Escape( $content ); ?></option><?php
                              }
                              else
                              {
                                ?><option value="<?php echo YamsUtils::Escape( $value ); ?>"><?php echo YamsUtils::Escape( $content ); ?></option><?php
                              }
                            }
                          ?></select><?php
                        }
                        else
                        {
                          if ( $modxLangName == '' )
                          {
                            echo '(none)';
                          }
                          else
                          {
                            echo YamsUtils::Escape( $modxLangName );
                          }
                        }
                            break;
                          case 'add':
                          default:
                            if ( $modxLangName == '' )
                            {
                              echo '(none)';
                            }
                            else
                            {
                              echo YamsUtils::Escape( $modxLangName );
                            }
                        }
                        ?></td><?php
                      }
                      if ( $mode == 'add' )
                      {
                        ?><td class="<?php echo $rowClass; ?>"><!--<input name="yams_add_modx_lang_name" type="text" />--><select name="yams_add_modx_lang_name"><?php
                            foreach ( $modxLangArray as $value => $content )
                            {
                              ?><option value="<?php echo YamsUtils::Escape( $value ); ?>"><?php echo YamsUtils::Escape( $content ); ?></option><?php
                            }
                          ?></select></td><?php
                      }
                      ?>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-SelectLanguageText" target="_blank">Select Language Text</a></th><?php
                    foreach ( $allLangIds as $langId )
                    {
                      $chooseLangText = $yams->GetChooseLangText( $langId );
                    ?><td class="<?php echo $rowClass; ?>"><?php
                      switch ( $mode )
                      {
                        case 'edit_multi':
                          if ( $langId == $edit_lang )
                          {
                            ?><input name="yams_edit_choose_lang_text" type="text" value="<?php echo YamsUtils::Escape( $chooseLangText ); ?>"/><?php
                          }
                          else
                          {
                            echo YamsUtils::Escape( $chooseLangText );
                          }
                          break;
                        case 'add':
                        default:
                          echo YamsUtils::Escape( $chooseLangText );
                      }
                    ?></td><?php
                  }
                  if ( $mode == 'add' )
                  {
                    ?><td class="<?php echo $rowClass; ?>"><input name="yams_add_choose_lang_text" type="text" /></td><?php
                  }
                    ?>
                  </tr>
                    <?php
                    foreach ( $allLangIds as $whichLangId )
                    {
                      ?>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-NameforlangId" target="_blank">Name for <?php echo $whichLangId; ?></a></th><?php
                      foreach ( $allLangIds as $inLangId )
                      {
                        $name = $yams->GetLangName( $inLangId, $whichLangId );
                        ?><td class="<?php echo $rowClass; ?>"><?php
                        switch ( $mode )
                        {
                          case 'edit_multi':
                            if ( $inLangId == $edit_lang )
                            {
                                ?><input name="yams_edit_name_<?php echo YamsUtils::Escape( $whichLangId ); ?>" type="text" value="<?php echo YamsUtils::Escape( $name ); ?>"/><?php
                              }
                              else
                              {
                                echo YamsUtils::Escape( $name );
                              }
                              break;
                          case 'add':
                          default:
                            echo YamsUtils::Escape( $name );
                          }
                          ?></td><?php
                        }
                        if ( $mode == 'add' )
                        {
                          ?><td class="<?php echo $rowClass; ?>"><input name="yams_add_name_<?php echo YamsUtils::Escape( $whichLangId ); ?>" type="text" /></td><?php
                        }
                        ?>
                  </tr>
                      <?php
                      }
                      ?>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Language+Settings#YAMSLanguageSettings-Namefornew" target="_blank">Name for new</a></th><?php
                    foreach ( $allLangIds as $inLangId )
                    {
                      ?><td class="<?php echo $rowClass; ?>"></td><?php
                    }
                    if ( $mode == 'add' )
                    {
                      ?><td class="<?php echo $rowClass; ?>"><input name="yams_add_name_new" type="text" /></td><?php
                    }
                    ?>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="tab-page" id="tabServerConfig">
              <h2 class="tab">Server Config</h2>
              <script type="text/javsacript">tpResources.addTabPage( document.getElementById( 'tabTemplates') );</script>
              <p>The following text can be used to replace the friendly URLs sections of the MODx .htaccess file.</p>
              <div>
                <code><?php echo preg_replace(
                    '/' . preg_quote( PHP_EOL, '/' ) . '/' . $yams->GetEncodingModifier()
                    , '<br />'
                    , YamsUtils::Escape(
                        $yams->GetFriendlyURLConfig()
                        , TRUE
                        , TRUE
                        , FALSE
                      )
                    ); ?></code>
              </div>
              <?php if ( $yams->GetUseLanguageDependentServerNames() )
              {
              ?>
              <p>If server name mode is active, then one possibility for
              setting up the different (sub)domains would be to set-up a virtual
              host with the following:</p>
              <div>
                <code><?php echo preg_replace(
                    '/' . preg_quote( PHP_EOL, '/' ) . '/' . $yams->GetEncodingModifier()
                    , '<br />'
                    , YamsUtils::Escape(
                        $yams->GetServerConfig()
                        , TRUE
                        , TRUE
                        , FALSE
                      )
                    ); ?></code>
              </div>
              <?php
              }
              ?>
            </div>

            <div class="tab-page" id="tabOtherParams">
              <h2 class="tab">Configuration</h2>
              <script type="text/javsacript">tpResources.addTabPage( document.getElementById( 'tabOtherParams') );</script>
              <table class="grid" summary="Other Parameters">
                <caption>Configuration Options</caption>
                <thead>
                  <tr>
                    <th class="gridHeader">Setting</th>
                    <th class="gridHeader">Name and Link to Documentation</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th class="gridHeader" colspan="2"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-MultilingualAliases" target="_blank">Multilingual Aliases</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <?php
                  $yesText = '';
                  $noText = '';
                  if ( $yams->GetMultilingualAliasesAreUnique() )
                  {
                    $yesText = 'selected="selected"';
                  }
                  else
                  {
                    $noText = 'selected="selected"';
                  }
                  ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_multilingual_aliases_are_unique">
                        <option <?php echo $yesText; ?> value="1">Unique</option>
                        <option <?php echo $noText; ?> value="0">Not Unique</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-UniquenessofMultilingualAliases" target="_blank">Uniqueness of Multilingual Aliases</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <?php
                  $yesText = '';
                  $noText = '';
                  if ( $yams->GetUseMultilingualAliases() )
                  {
                    $yesText = 'selected="selected"';
                  }
                  else
                  {
                    $noText = 'selected="selected"';
                  }
                  ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_use_multilingual_aliases">
                        <option <?php echo $yesText; ?> value="1">Yes</option>
                        <option <?php echo $noText; ?> value="0">No</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-UseMultilingualAliases" target="_blank">Use Multilingual Aliases?</a></th>
                  </tr>
                  <tr>
                    <td align="left" colspan="3"><button name="yams_action" type="submit" value="submit_other_params" >Submit</button></td>
                  </tr>
                </tbody>
                <tbody>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="gridHeader" colspan="2"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-URLRedirectionSettings" target="_blank">URL Redirection Settings</a></th>
                  </tr>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <?php
                        $modeNone = '';
                        $modeCurrent = '';
                        $modeDefault = '';
                        $modeBrowser = '';
                        $modeCurrentElseBrowser = '';
                        switch ( $yams->GetRedirectionMode() )
                        {
                          case 'default':
                            $modeDefault = 'selected="selected"';
                            break;
                          case 'current':
                            $modeCurrent = 'selected="selected"';
                            break;
                          case 'current_else_browser':
                            $modeCurrentElseBrowser = 'selected="selected"';
                            break;
                          case 'browser':
                            $modeBrowser = 'selected="selected"';
                            break;
                          case 'none':
                          default:
                            $modeNone = 'selected="selected"';
                        }
                        ?>
                      <select name="yams_redirection_mode">
                        <option <?php echo $modeNone; ?>  value="none" >None</option>
                        <option <?php echo $modeDefault; ?>  value="default" >Default</option>
                        <option <?php echo $modeCurrent; ?>  value="current" >Current</option>
                        <option <?php echo $modeCurrentElseBrowser; ?>  value="current_else_browser" >Current else Browser</option>
                        <option <?php echo $modeBrowser; ?>  value="browser" >Browser</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-RedirectionMode" target="_blank">Redirection Mode</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
<?php
                        $status = array(
                            300 => ''
                            , 301 => ''
                            , 302 => ''
                            , 303 => ''
                            , 307 => ''
                        );
                        $status[ $yams->GetHTTPStatus() ] = 'selected="selected"';
?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_http_status">
                        <option <?php echo $status[300]; ?> value="300">multiple choices (300)</option>
                        <option <?php echo $status[301]; ?> value="301">permanent (301)</option>
                        <option <?php echo $status[302]; ?> value="302">found (302)</option>
                        <option <?php echo $status[303]; ?> value="303">see other (303)</option>
                        <option <?php echo $status[307]; ?> value="307">temporary (307)</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-StatusCodeforRedirectiontoPagesintheDefaultLanguage" target="_blank">Status Code for Redirection to Pages in the Default Language</a></th>
                  </tr><?php
                      $hideText = '';
                      $showText = '';
                      if ( $yams->GetHideFields() )
                      {
                        $hideText = 'selected="selected"';
                      }
                      else
                      {
                        $showText = 'selected="selected"';
                      }
                      ?>
<?php YamsAlternateRow( $rowClass ); ?>
<?php
                        $statusNotDefault = array(
                            300 => ''
                            , 301 => ''
                            , 302 => ''
                            , 303 => ''
                            , 307 => ''
                        );
                        $statusNotDefault[ $yams->GetHTTPStatusNotDefault() ] = 'selected="selected"';
?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_http_status_not_default">
                        <option <?php echo $statusNotDefault[300]; ?> value="300">multiple choices (300)</option>
                        <option <?php echo $statusNotDefault[301]; ?> value="301">permanent (301)</option>
                        <option <?php echo $statusNotDefault[302]; ?> value="302">found (302)</option>
                        <option <?php echo $statusNotDefault[303]; ?> value="303">see other (303)</option>
                        <option <?php echo $statusNotDefault[307]; ?> value="307">temporary (307)</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-StatusCodeforRedirectiontoPagesinNonDefaultLanguages" target="_blank">Status Code for Redirection to Pages in Non-Default Languages</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
<?php
                  $statusChangeLang = array(
                      300 => ''
                      , 301 => ''
                      , 302 => ''
                      , 303 => ''
                      , 307 => ''
                  );
                  $statusChangeLang[ $yams->GetHTTPStatusChangeLang() ] = 'selected="selected"';
?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_http_status_change_lang">
                        <option <?php echo $statusChangeLang[300]; ?> value="300">multiple choices (300)</option>
                        <option <?php echo $statusChangeLang[301]; ?> value="301">permanent (301)</option>
                        <option <?php echo $statusChangeLang[302]; ?> value="302">found (302)</option>
                        <option <?php echo $statusChangeLang[303]; ?> value="303">see other (303)</option>
                        <option <?php echo $statusChangeLang[307]; ?> value="307">temporary (307)</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-StatusCodeforChangeofLanguage" target="_blank">Status Code for Change of Language</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
<?php
                  $acceptMODxURLDocIdsString = htmlspecialchars( $yams->GetAcceptMODxURLDocIdsString() );
?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <input type="text" name="yams_accept_modx_url_doc_ids" value="<?php echo $acceptMODxURLDocIdsString; ?>" />
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-MODxURLs" target="_blank">MODx URLs</a></th>
                  </tr>
                  <tr>
                    <td align="left" colspan="3"><button name="yams_action" type="submit" value="submit_other_params" >Submit</button></td>
                  </tr>
                </tbody>
                <tbody>
                  <tr>
                    <th class="gridHeader" colspan="2"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-DocumentLayoutSettings" target="_blank">Document Layout Settings</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <?php
                      $hideText = '';
                      $showText = '';
                      if ( $yams->GetHideFields() )
                      {
                        $hideText = 'selected="selected"';
                      }
                      else
                      {
                        $showText = 'selected="selected"';
                      }
                      ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_hide_fields">
                        <option <?php echo $hideText; ?> value="1">Hide Fields</option>
                        <option <?php echo $showText; ?> value="0">Show Fields</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-HideRedundantFields" target="_blank">Hide Redundant Fields</a></th>
                  </tr>
                  <?php
                  $listText = '';
                    $tabifyText = '';
                    if ( $yams->GetTabifyLangs() )
                    {
                      $tabifyText = 'selected="selected"';
                    }
                    else
                    {
                      $listText = 'selected="selected"';
                    }
                  ?>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_tabify_langs">
                        <option <?php echo $tabifyText; ?> value="1">Tabify TVs by Lang</option>
                        <option <?php echo $listText; ?> value="0">List TVs</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-DocumentLayout" target="_blank">Document Layout</a></th>
                  </tr>
                  <?php
                  $yesText = '';
                  $noText = '';
                  if ( $yams->GetSynchronisePagetitle() )
                  {
                    $yesText = 'selected="selected"';
                  }
                  else
                  {
                    $noText = 'selected="selected"';
                  }
                  ?>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_synchronise_pagetitle">
                        <option <?php echo $yesText; ?> value="1">Yes</option>
                        <option <?php echo $noText; ?> value="0">No</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-AutoupdateManagerDocumentTitle" target="_blank">Autoupdate Manager Document Title</a></th>
                  </tr>
                  <tr>
                    <td align="left" colspan="3"><button name="yams_action" type="submit" value="submit_other_params" >Submit</button></td>
                  </tr>
                </tbody>
                <tbody>
                  <tr>
                    <th class="gridHeader" colspan="2"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-URLFormatting" target="_blank">URL Formatting</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <?php
                  $yesText = '';
                  $noText = '';
                  if ( $yams->GetUseStripAlias() )
                  {
                    $yesText = 'selected="selected"';
                  }
                  else
                  {
                    $noText = 'selected="selected"';
                  }
                  ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_use_strip_alias">
                        <option <?php echo $yesText; ?> value="1">Yes</option>
                        <option <?php echo $noText; ?> value="0">No</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-UseMODxstripAlias" target="_blank">Use MODx stripAlias</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <?php
                  $yesText = '';
                  $noText = '';
                  if ( $yams->GetUseMimeDependentSuffixes() )
                  {
                    $yesText = 'selected="selected"';
                  }
                  else
                  {
                    $noText = 'selected="selected"';
                  }
                  ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_use_mime_dependent_suffixes">
                        <option <?php echo $yesText; ?> value="1">Yes</option>
                        <option <?php echo $noText; ?> value="0">No</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-UseMimetypedependentsuffixes%3F" target="_blank">Use Mime-type dependent suffixes?</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <?php
                  $yesText = '';
                  $noText = '';
                  if ( $yams->GetShowSiteStartAlias() )
                  {
                    $yesText = 'selected="selected"';
                  }
                  else
                  {
                    $noText = 'selected="selected"';
                  }
                  ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_show_site_start_alias">
                        <option <?php echo $yesText; ?> value="1">Include filename</option>
                        <option <?php echo $noText; ?> value="0">Don't include filename</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-Sitestartfilename" target="_blank">Site start filename</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <?php
                  $yesText = '';
                  $noText = '';
                  if ( $yams->GetRewriteContainersAsFolders() )
                  {
                    $yesText = 'selected="selected"';
                  }
                  else
                  {
                    $noText = 'selected="selected"';
                  }
                  ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_rewrite_containers_as_folders">
                        <option <?php echo $yesText; ?> value="1">Rewrite as folders</option>
                        <option <?php echo $noText; ?> value="0">Leave as files</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-Containersasfolders" target="_blank">Containers as folders</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <?php
                        $langQueryParam = $yams->GetLangQueryParam();
                      ?>
                      <input name="yams_lang_query_param" type="text" value="<?php echo YamsUtils::Escape( $langQueryParam ); ?>"></input>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-ConfirmLanguageParam" target="_blank">Confirm Language Param</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <?php
                      $changeLangQueryParam = $yams->GetChangeLangQueryParam();
                      ?>
                      <input name="yams_change_lang_query_param" type="text" value="<?php echo YamsUtils::Escape( $changeLangQueryParam ); ?>"></input>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-ChangeLanguageParam" target="_blank">Change Language Param</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <?php
                      $MODxSubdirectory = $yams->GetMODxSubdirectory( FALSE, FALSE, FALSE );
                      ?>
                      <input name="yams_modx_subdirectory" type="text" value="<?php echo YamsUtils::Escape( $MODxSubdirectory ); ?>"></input>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-MODxSubdirectory" target="_blank">MODx Subdirectory</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <?php
                        $modeNone = '';
                        $modeDefault = '';
                        $modeResolve = '';
                        switch ( $yams->GetURLConversionMode() )
                        {
                          case 'default':
                            $modeDefault = 'selected="selected"';
                            break;
                          case 'resolve':
                            $modeResolve = 'selected="selected"';
                            break;
                          case 'none':
                          default:
                            $modeNone = 'selected="selected"';
                        }
                        ?>
                      <select name="yams_url_conversion_mode">
                        <option <?php echo $modeNone; ?>  value="none" >None</option>
                        <option <?php echo $modeDefault; ?>  value="default" >Default</option>
                        <option <?php echo $modeResolve; ?>  value="resolve" >Resolve</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-URLConversionMode" target="_blank">URL Conversion Mode</a></th>
                  </tr>
                  <tr>
                    <td align="left" colspan="3"><button name="yams_action" type="submit" value="submit_other_params" >Submit</button></td>
                  </tr>
                </tbody>
                <tbody>
                  <tr>
                    <th class="gridHeader" colspan="2"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-EasylingualCompatibility" target="_blank">Easylingual Compatibility</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <?php
                  $yesText = '';
                  $noText = '';
                  if ( $yams->GetEasyLingualCompatibility() )
                  {
                    $yesText = 'selected="selected"';
                  }
                  else
                  {
                    $noText = 'selected="selected"';
                  }
                  ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <select name="yams_easylingual_compatibility">
                        <option <?php echo $yesText; ?> value="1">Yes</option>
                        <option <?php echo $noText; ?> value="0">No</option>
                      </select>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://svn.modxcms.com/docs/display/ADDON/YAMS+Configuration#YAMSConfiguration-EasylingualCompatibilityMode" target="_blank">Easylingual Compatibility Mode</a></th>
                  </tr>
                  <tr>
                    <td align="left" colspan="3"><button name="yams_action" type="submit" value="submit_other_params" >Submit</button></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="tab-page" id="tabTemplates">
              <h2 class="tab">Multilingual Templates</h2>
              <script type="text/javsacript">tpResources.addTabPage( document.getElementById( 'tabTemplates') );</script><?php
                      $limit = $yams->GetTemplateInfo( $info );
                      if( $limit < 1 )
                      {
                        echo $_lang['no_results'];
                      }
                      $preCat = '';

                      ?><h3>Template Variable Management</h3>
<p>The table allow allows templates to be specified as monolingual (the default for new templates) or multilingual. For multilingual templates, selecting 'Update' will: </p>
                      <ul>
                        <li>automatically create the multilingual template variable
                          versions of the normal document variables; </li>
                        <li>copy over content from the document variables to newly
                          created multilingual template variables for the default
                          language; and</li>
                        <li>associate and disassociate these with the selected templates
                          as required.</li>
                      </ul><p>When using the YAMS ManagerManager rules documents with multilingual templates will have a tabbed language interface.</p>
                      <p>The multilingual template variables have the same names
                         as their document variable counterparts, but with <code>_<em>id</em></code>
                         appended, where <code><em>id</em></code> is the language id.
                         For example <code>menutitle_en</code> or <code>content_fr</code>.
                         Multi-language versions of the following fields are created:
                         pagetitle, longtitle, description, introtext, menutitle and content.</p>
                       <p>YAMS will never delete any template variables. If a language is created and subsequently deleted any template variables that have been created for that language will have to be deleted manually.</p>
              <table class="grid" summary="Active Templates">
                <caption>Multilingual Templates</caption>
                <thead>
                  <tr>
                    <th class="gridHeader">Multilingual?</th>
                    <th class="gridHeader">Template</th>
                    <th class="gridHeader">Description</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td align="left" colspan="3"><button name="yams_action" type="submit" value="submit_templates">Update</button></td>
                  </tr>
                  <?php

                  $insideUl = 0;
                  for( $i=0; $i<$limit; $i++ )
                  {
                    $row = mysql_fetch_assoc( $info );
                    $yes = '';
                    $no = 'selected="selected"';
                    if ( in_array( $row[ 'id' ], $yams->GetActiveTemplates() ) )
                    {
                      $yes = 'selected="selected"';
                                        $no = '';
                                      }
                                      ?>
                  <?php YamsAlternateRow( $rowClass ); ?>
                                    <tr>
                                      <td class="<?php echo $rowClass; ?>">
                                        <select name="template,<?php echo $row['id']; ?>" >
                                          <option value="1" <?php echo $yes; ?> >yes</option>
                                          <option value="0" <?php echo $no; ?> >no</option>
                                        </select>
                                      </td>
                                      <td class="<?php echo $rowClass; ?>"><?php echo $row['name']; ?> (<?php echo $row['id']; ?>)</td>
                                      <td class="<?php echo $rowClass; ?>"><?php echo !empty( $row['description'] ) ? $row['description'] : '' ; ?></td>
                                    </tr><?php
                    $preCat = $row['category'];
                  }
// $output .= $insideUl? '</ul>': '';
// echo $output;
/*
  */
                  ?>
                  <tr>
                    <td align="left" colspan="3"><button name="yams_action" type="submit" value="submit_templates">Update</button></td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </fieldset>
    </form>
  </body>
</html>