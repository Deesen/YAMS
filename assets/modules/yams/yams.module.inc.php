<?php
/**
 * YAMS
 *
 * Yet Another Multilingual Solution
 *
 * @category 	snippet
 * @version 	1.2.0 RC7
 * @license 	GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @internal	@properties
 * @internal	@modx_category YAMS
 * @internal    @installset base
 * @documentation README.md [+site_url+]assets/modules/YAMS/README.md
 * @documentation http://www.evolution-docs.com/extras/yams/
 * @reportissues https://github.com/Deesen/YAMS
 * @link        http://modxcms.com/forums/index.php/board,381.0.html
 * @author      PMS http://modxcms.com/forums/index.php?action=profile;u=12570
 * @author      Nashi Power
 * @copyright   Nashi Power
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
          $row = $modx->db->getRow( $info );
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

	// Prepare category-ID for TVs
	$categoryId = checkCategory('YAMS');
	$categoryId = $categoryId ? $categoryId : newCategory('YAMS');

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
              , 'category' => $categoryId
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
          YamsAddAssociationForTV( $tvId, $templateId );
          $newlyAssociatedTV = TRUE;
          $errorText[] = 'Associated template variable "' . $multiLangTV . '" with template number ' . $templateId . ' .';
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
        if (is_countable($associatedInactiveTemplates) && ( count($associatedInactiveTemplates) > 0 ))
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

            if (is_countable($activeTemplates) && ( count( $activeTemplates ) > 0 ))
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
              $nDocs = $modx->db->getRecordCount( $result );
              for ( $i = 0; $i < $nDocs; $i++ )
              {
                $row = $modx->db->getRow( $result );
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
                $selectNRows = $modx->db->getRecordCount( $selectResult );
                if ( $selectNRows > 1)
                {
                  // error, there should only be 1 result...
                  continue;
                }
                if ( $selectNRows == 1 )
                {
                  $row = $modx->db->getRow( $selectResult );
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

            if (is_countable($activeTemplates) && ( count( $activeTemplates ) > 0 ))
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
              $nDocs = $modx->db->getRecordCount( $result );
              for ( $i = 0; $i < $nDocs; $i++ )
              {
                $row = $modx->db->getRow( $result );
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
                $selectNRows = $modx->db->getRecordCount( $selectResult );
                if ( $selectNRows > 1)
                {
                  // error, there should only be 1 result...
                  continue;
                }
                if ( $selectNRows == 1 )
                {
                  $row = $modx->db->getRow( $selectResult );
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
if (is_countable($errorText) && ( count( $errorText ) > 0 ))
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
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $modx->getConfig('manager_direction'); ?>" xml:lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $modx->getConfig('modx_charset'); ?>" />
    <title>YAMS Module Configuration</title>
    <link rel="stylesheet" type="text/css" href="media/style/<?php echo $modx->getConfig('manager_theme'); ?>/style.css" ></link>
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
              <dt><strong>Version</strong></dt> <dd><?php echo $yams->GetVersion(); ?>. Check <a href="https://github.com/Deesen/YAMS/releases" target="_blank">Github</a> for the latest version.</dd>
              <dt><strong>Author</strong></dt> <dd><a href="http://modxcms.com/forums/index.php?action=profile;u=12570" target="_blank">PMS</a><br />Original multilingual alias code supplied by <a href="http://modxcms.com/forums/index.php?action=profile;u=21916" target="_blank">mgbowman</a></dd>
              <dt><strong>Documentation</strong></dt> <dd><a target="_blank" href="http://docs.evo.im/en/04_extras/yams.html">YAMS documentation</a>, including setup instructions, placeholders, snippet parameters and How To? guides are now maintained on GitHub</dd>
              <dt><strong>Licence</strong></dt> <dd>GPL v3</dd>
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
              content. It is possible to replace the
              placeholders by special YAMS snippet calls, eg:
                <br />
                <code>[[YAMS? &amp;get=`data` &amp;from=`pagetitle` &amp;docid=`[+id+]`]]</code><br /></p>
              <p>As of version 1.0.3, YAMS *should be* fully compatible with <a href="http://modxcms.com/forums/index.php/topic,32807.0.html" target="_blank">EasyLingual</a>. See the Setup tab for instructions on how to migrate a site from EasyLingual to YAMS.</p>
              <p>YAMS has been developed on MODx Evolution v0.9.6.3 and has been upgraded to ensure compatibility with Evolution CMS 1.4x and 2.x and with the latest version of PHP (7.x). Latests YAMS v1.3.0 will not work with sites running PHP 4 an Evolution CMS v1.3.x and lower.</p></dd>
              <dt><strong>Credits</strong></dt> <dd><p>The language icons used in the
              language select drop down are from <a href="http://www.languageicon.org/" target="_blank">Language Icon</a>,
              released under the <a href="http://creativecommons.org/licenses/by-sa/3.0/">Attribution-Share Alike 3.0 Unported</a> license.
              Thanks to @MadeMyDay for having the courage to be one of the first guinea pigs!</p>
              </dd>
              </dl>
              <p><strong>Version History</strong>:</p>
              <p>Latest history in README.md: <a href="https://github.com/Deesen/YAMS/blob/master/assets/modules/yams/README.md" target="_blank">https://github.com/Deesen/YAMS</a></p>
            </ >


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
                    <th class="<?php echo $rowClass;?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-MonoServerName" target="_blank">Server Name</a></th>
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
                    <th class="<?php echo $rowClass;?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-MonoSiteURL" target="_blank">Site URL</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-LangID" target="_blank">Lang ID</a></th><?php
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-Tags" target="_blank">Tags</a></th><?php
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-ServerName" target="_blank">Server Name</a></th><?php
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-RootName" target="_blank">Root Name</a></th><?php
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-SiteURL" target="_blank">Site URL</a></th><?php
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-LanguageDirection" target="_blank">Language Direction</a></th><?php
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-Roles" target="_blank">Roles</a></th><?php
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-MODxLanguageName" target="_blank">MODx Language Name</a></th><?php
                    // Get the listing of all MODx langs...
                    $modxLangFiles = scandir(
                        $modx->getConfig('base_path')
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-SelectLanguageText" target="_blank">Select Language Text</a></th><?php
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-NameforlangId" target="_blank">Name for <?php echo $whichLangId; ?></a></th><?php
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-language-settings#YAMSLanguageSettings-Namefornew" target="_blank">Name for new</a></th><?php
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
                    <th class="gridHeader" colspan="2"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-MultilingualAliases" target="_blank">Multilingual Aliases</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-UniquenessofMultilingualAliases" target="_blank">Uniqueness of Multilingual Aliases</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-UseMultilingualAliases" target="_blank">Use Multilingual Aliases?</a></th>
                  </tr>
                  <tr>
                    <td align="left" colspan="3"><button name="yams_action" type="submit" value="submit_other_params" >Submit</button></td>
                  </tr>
                </tbody>
                <tbody>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <th class="gridHeader" colspan="2"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-URLRedirectionSettings" target="_blank">URL Redirection Settings</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-RedirectionMode" target="_blank">Redirection Mode</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-StatusCodeforRedirectiontoPagesintheDefaultLanguage" target="_blank">Status Code for Redirection to Pages in the Default Language</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-StatusCodeforRedirectiontoPagesinNonDefaultLanguages" target="_blank">Status Code for Redirection to Pages in Non-Default Languages</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-StatusCodeforChangeofLanguage" target="_blank">Status Code for Change of Language</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
<?php
                  $acceptMODxURLDocIdsString = htmlspecialchars( $yams->GetAcceptMODxURLDocIdsString() );
?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <input type="text" name="yams_accept_modx_url_doc_ids" value="<?php echo $acceptMODxURLDocIdsString; ?>" />
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-MODxURLs" target="_blank">MODx URLs</a></th>
                  </tr>
                  <tr>
                    <td align="left" colspan="3"><button name="yams_action" type="submit" value="submit_other_params" >Submit</button></td>
                  </tr>
                </tbody>
                <tbody>
                  <tr>
                    <th class="gridHeader" colspan="2"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-DocumentLayoutSettings" target="_blank">Document Layout Settings</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-HideRedundantFields" target="_blank">Hide Redundant Fields</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-DocumentLayout" target="_blank">Document Layout</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-AutoupdateManagerDocumentTitle" target="_blank">Autoupdate Manager Document Title</a></th>
                  </tr>
                  <tr>
                    <td align="left" colspan="3"><button name="yams_action" type="submit" value="submit_other_params" >Submit</button></td>
                  </tr>
                </tbody>
                <tbody>
                  <tr>
                    <th class="gridHeader" colspan="2"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-URLFormatting" target="_blank">URL Formatting</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-UseMODxstripAlias" target="_blank">Use MODx stripAlias</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-UseMimetypedependentsuffixes%3F" target="_blank">Use Mime-type dependent suffixes?</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-Sitestartfilename" target="_blank">Site start filename</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-Containersasfolders" target="_blank">Containers as folders</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <?php
                        $langQueryParam = $yams->GetLangQueryParam();
                      ?>
                      <input name="yams_lang_query_param" type="text" value="<?php echo YamsUtils::Escape( $langQueryParam ); ?>"></input>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-ConfirmLanguageParam" target="_blank">Confirm Language Param</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <?php
                      $changeLangQueryParam = $yams->GetChangeLangQueryParam();
                      ?>
                      <input name="yams_change_lang_query_param" type="text" value="<?php echo YamsUtils::Escape( $changeLangQueryParam ); ?>"></input>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-ChangeLanguageParam" target="_blank">Change Language Param</a></th>
                  </tr>
<?php YamsAlternateRow( $rowClass ); ?>
                  <tr>
                    <td class="<?php echo $rowClass; ?>">
                      <?php
                      $MODxSubdirectory = $yams->GetMODxSubdirectory( FALSE, FALSE, FALSE );
                      ?>
                      <input name="yams_modx_subdirectory" type="text" value="<?php echo YamsUtils::Escape( $MODxSubdirectory ); ?>"></input>
                    </td>
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-MODxSubdirectory" target="_blank">EVO Subdirectory</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-URLConversionMode" target="_blank">URL Conversion Mode</a></th>
                  </tr>
                  <tr>
                    <td align="left" colspan="3"><button name="yams_action" type="submit" value="submit_other_params" >Submit</button></td>
                  </tr>
                </tbody>
                <tbody>
                  <tr>
                    <th class="gridHeader" colspan="2"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-EasylingualCompatibility" target="_blank">Easylingual Compatibility</a></th>
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
                    <th class="<?php echo $rowClass; ?>"><a href="http://www.evolution-docs.com/extras/yams/yams-configuration#YAMSConfiguration-EasylingualCompatibilityMode" target="_blank">Easylingual Compatibility Mode</a></th>
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
                    $row = $modx->db->getRow( $info );
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
