<?php
/**
 * ManagerManager rules for formatting the document interface so as to display
 * different languages on different tabs
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */

require_once( dirname( __FILE__ ) . '/class/yams.class.inc.php' );
require_once( dirname( __FILE__ ) . '/yams.module.funcs.inc.php' );

$yams = YAMS::GetInstance();

$activeTemplateList = $yams->GetActiveTemplatesList();
$hideFields = $yams->GetHideFields();
$useMultilingualAliases = $yams->GetUseMultilingualAliases();
$encodingModifier = $yams->GetEncodingModifier();

$langIds = array_merge(
  $yams->GetActiveLangIds()
  , $yams->GetInactiveLangIds()
  );
  
//// yamsRoles is an array mapping which applies roles to each language
//if ( ! is_array( $yamsLanguageRoleMap ) )
//{
//  $yamsLanguageRoleMap = array();
//}
//
if ( $activeTemplateList != '' )
{

  mm_renameField(
    'pagetitle'
    , 'Internal Name'
    , ''
    , $activeTemplateList
    , 'This field is used to identify the document (and its language variants) within the document tree of the MODx Manager. It will not appear anywhere on the document itself.');

  if ( $hideFields )
  {
    // Don't hide the pagetitle because it is used within the modx document tree
    // to identify the page
    mm_hideFields(
      'longtitle,description,introtext,menutitle,content'
      , ''
      , $activeTemplateList
      );
    mm_hideSections(
      'content'
      , ''
      , $activeTemplateList
      );
    if ( $useMultilingualAliases )
    {
      mm_hideFields(
        'alias'
        , ''
        , $activeTemplateList
        );
    }
    else
    {
      $aliasesToHideArray = array();
      foreach ( $langIds as $langId )
      {
        $aliasesToHideArray[]
          = YAMSTVDataToMMName( 'alias_' . $langId, $id, 'tv', $mm_version );
      }
      if ( count( $aliasesToHideArray ) > 0 )
      {
        $aliasesToHide = implode( ',', $aliasesToHideArray );
        mm_hideFields(
          $aliasesToHide
          , ''
          , $activeTemplateList
          );
      }
      unset( $aliasesToHideArray );
    }
  }

  if ( $yams->GetTabifyLangs() )
  {
    foreach ( $langIds as $langId )
    {
      $rolesAccessList = $yams->GetRolesAccessList( $langId );
      $rolesNoAccessList = $yams->GetRolesNoAccessList( $langId );
      
      $langName = $yams->GetLangName( $langId );
      mm_createTab(
        $langName
        , $langId
        , $rolesAccessList
        , $activeTemplateList
        );
      // Get the names and ids of all the multilingual template variables
      // ending in _{langid}
      $result = $modx->db->select(
        'id,name,rank'
        , $modx->getFullTableName('site_tmplvars')
        , 'name LIKE \'%\_' . $modx->db->escape( $langId ) . '\''
        );
      $nRows = $modx->db->getRecordCount( $result );
      
      // Loop over the multilingual tvs and sort into custom and standard.
      // The standard ones are ordered in the normal order.
      // The custom ones are ordered as is (by probably should respect the
      // tv order specified in the database...)
      // For each tv, calculate the name required by ManagerManager
      $standardTVs = array();
      $customTVs = array();
      for ( $i = 0; $i < $nRows; $i++ )
      {
        $idNameArray = $modx->db->getRow( $result );
        $name = $idNameArray['name'];
        $id = $idNameArray['id'];
        $rank = intval( $idNameArray['rank'] );
        switch ( $name )
        {
          case 'pagetitle_' . $langId:
            $standardTVs['1'] = YAMSTVDataToMMName( $name, $id, 'tv', $mm_version );
            break;
          case 'longtitle_' . $langId:
            $standardTVs['2'] = YAMSTVDataToMMName( $name, $id, 'tv', $mm_version );
            break;
          case 'description_' . $langId:
            $standardTVs['3'] = YAMSTVDataToMMName( $name, $id, 'tv', $mm_version );
            break;
          case 'alias_' . $langId:
            if ( $useMultilingualAliases )
            {
              $standardTVs['4'] = YAMSTVDataToMMName( $name, $id, 'tv', $mm_version );
            }
            break;
          case 'introtext_' . $langId:
            $standardTVs['5'] = YAMSTVDataToMMName( $name, $id, 'tv', $mm_version );
            break;
          case 'menutitle_' . $langId:
            $standardTVs['6'] = YAMSTVDataToMMName( $name, $id, 'tv', $mm_version );
            break;
          case 'content_' . $langId:
            $standardTVs['7'] = YAMSTVDataToMMName( $name, $id, 'tv', $mm_version );
            break;
          default:
            if ( array_key_exists( $rank, $customTVs) )
            {
              $customTVs[] = YAMSTVDataToMMName( $name, $id, 'tv', $mm_version );
            }
            else
            {
              $customTVs[$rank] = YAMSTVDataToMMName( $name, $id, 'tv', $mm_version );
            }
        }
      }
      // sort the standard tvs
      ksort( $standardTVs );
      ksort( $customTVs );

      $nStandardTVs = count( $standardTVs );
      $nCustomTVs = count( $customTVs );

      if ( $nStandardTVs == 0 && $nCustomTVs == 0 )
      {
        continue;
      }

      // Convert to list...
      $tvString = '';
      if ( $nStandardTVs > 0 )
      {
        $tvString .= implode( ',', $standardTVs );
      }
      if ( $nCustomTVs > 0 )
      {
        if ( $nStandardTVs > 0 )
        {
          $tvString .= ',';
        }
        $tvString .= implode( ',', $customTVs );
      }
      // $modx->logEvent( 4, 1, 'Moving tvs to tab ' . $langId . ': ' . $tvString, 'YAMS mm_rules' );

      mm_moveFieldsToTab(
        $tvString
        , $langId
        , $rolesAccessList
        , $activeTemplateList
        );
      if ( $rolesNoAccessList != '!' )
      {
        mm_hideFields(
          $tvString
          , $rolesNoAccessList
          , $activeTemplateList
          );
      }
    }
  }

}

?>