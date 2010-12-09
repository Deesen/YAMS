<?php
/**
 * Miscellaneous functions used by the YAMS module interface
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */

require_once( dirname( __FILE__ ) . '/class/yams.class.inc.php' );

if ( !function_exists( 'YAMSGetTempVarId' ) )
{
  function YAMSGetTempVarId( $name ) 
  {
    global $modx;
    // Get the id of the template variable from the name
    $result = $modx->db->select(
      'id'
      , $modx->getFullTableName('site_tmplvars')
      , 'name=\'' . $modx->db->escape( $name ) . '\''
      );
    $nRows = $modx->db->getRecordCount( $result );
    if ( $nRows != 1 )
    {
      // Error, should have found one...
      return FALSE;
    }
    $idArray = $modx->db->getRow( $result );
    return $idArray[ 'id' ];
  }
}

if ( ! function_exists( 'YAMSTVDataToMMName') )
{
  function YAMSTVDataToMMName( $name, $id, $prefix = 'tv', $mmVersion = NULL )
  {
    global $modx;
    $yams = YAMS::GetInstance();
    // Work out which version of MODx we are using.
    $versionData = $modx->getVersionData();
    $version = $versionData[ 'version' ];
    // $full_version = $versionData[ 'full_version' ];
    if (
      preg_match(
        '/^(0\.|1\.0\.0\-RC1)/' . $yams->GetEncodingModifier()
        , $version
        )
        == 1
      )
    {
      // old style references
      $additionalEncodings = array('-' => '%2D', '.' => '%2E', '_' => '%5F');
      return $prefix . str_replace(
        array_keys($additionalEncodings)
        , array_values($additionalEncodings)
        , rawurlencode( $name )
        );
//      return 'tv'
//        . preg_replace(
//          '/_/' . $yams->GetEncodingModifier()
//          , '%5F'
//          , $name
//          );
    }
    else
    {
      if ( is_null( $mmVersion ) )
      {
        // new style references
        return $prefix . $id;
      }
      // If a managermanager version has been specified, then
      // the output depends on the version...
      if ( version_compare( $mmVersion, '0.3.4', '<' ) )
      {
        // Older versions of managermanager required an id
        return $prefix . $id;
      }
      else
      {
        // but newer versions require a name...
        return $prefix . $name;
      }
    }
  }
}

if ( !function_exists( 'YamsAlternateRow' ) )
{
  function YamsAlternateRow( &$rowClass )  
  {
    switch ( $rowClass )
    {
    case 'gridItem':
      $rowClass = 'gridAltItem';
      return TRUE;
      break;
    case 'gridAltItem':
      $rowClass = 'gridItem';
      return TRUE;
      break;
    default:
      $rowClass = 'gridItem';
      return FALSE;
    }
  }
}

if ( !function_exists( 'YamsGetMODxTVs' ) )
{
  function YamsGetMODxTVs( &$modxTVs )
  {
    global $modx;
    $tblName = $modx->getFullTableName( 'site_tmplvars' );
    $result = $modx->db->select( 'name,id', $tblName );
    $nTVs = mysql_num_rows( $result );
    $modxTVs = array();
    for ( $i = 0; $i < $nTVs; $i++ )
    {
      $row = mysql_fetch_assoc( $result );
      $modxTVs[$row['name']] = $row['id'];
    }
    return $nTVs;
  }
}

if ( !function_exists( 'YamsGetMODxTemplatesForTV' ) )
{
  function YamsGetMODxTemplatesForTV( $tvId, &$templates )
  {
    global $modx;
    $tblName = $modx->getFullTableName( 'site_tmplvar_templates' );
    $result = $modx->db->select(
      'templateid,rank'
      , $tblName
      , 'tmplvarid=' . $modx->db->escape( $tvId )
      );
    $nTemplates = mysql_num_rows( $result );
    $templates = array();
    for ( $i = 0; $i < $nTemplates; $i++ )
    {
      $row = mysql_fetch_assoc( $result );
      $templates[$row['templateid']] = $row['rank'];
    }
    return $nTemplates;
  }
}

if ( !function_exists( 'YamsAddAssociationsForTV' ) )
{
  function YamsAddAssociationForTV( $tvId, $templateId )
  {
    global $modx;
    $yams = YAMS::GetInstance();
    if ( ! $yams->IsValidId( $tvId ) )
    {
      return FALSE;
    }
    if ( ! $yams->IsValidId( $templateId ) )
    {
      return FALSE;
    }
    $data = array(
      'tmplvarid' => $modx->db->escape( $tvId )
      , 'templateid' => $modx->db->escape( $templateId )
      );
    $tblName = $modx->getFullTableName( 'site_tmplvar_templates' );
    $result = $modx->db->insert(
      $data
      , $tblName
      );
    return $result;
  }
}

if ( !function_exists( 'YamsRemoveAssociationsForTV' ) )
{
  function YamsRemoveAssociationsForTV( $tvId, $templateIds )
  {
    global $modx;
    $yams = YAMS::GetInstance();
    if ( !is_array( $templateIds ) )
    {
      return FALSE;
    }
    if ( count( $templateIds ) == 0 )
    {
      return TRUE;
    }
    foreach ( $templateIds as &$templateId )
    {
      if ( ! $yams->IsValidId( $templateId ) )
      {
        return FALSE;
      }
      $templateId = $modx->db->escape( $templateId );
    }
    $templateIdList = implode(',', $templateIds);
    
    $tblName = $modx->getFullTableName( 'site_tmplvar_templates' );
    $result = $modx->db->delete(
      $tblName
      , 'tmplvarid=' . $modx->db->escape( $tvId )
        . ' AND '
        . 'templateid IN ('
        . $templateIdList
        . ')'
      );
    return $result;
  }
}

// if ( !function_exists( 'YamsCreateTV' ) )
// {
  // function YamsCreateTV(
  // $name
  // , $description
  // , $caption
  // , $type // 'text' or 'rawtext'
  // , $templateIds			  
  // )
  // {
    // global $modx;
    // $dbase = $modx->db->config['dbase'];
    // $table_prefix = $modx->db->config['table_prefix'];

    // $elements = '';
    // $default_text = '';
    // $display = '';
    // $params = '';
    // $rank = '0';
    // $locked = '0';
    // $categoryid = '';
    
    // $sql = 'INSERT INTO '
    // . $dbase
    // . '.`'
    // . $table_prefix
    // . 'site_tmplvars` (name, description, caption, type, elements, default_text, display,display_params, rank, locked, category) VALUES(\''
    // . $name . '\', \''
    // . $description
    // . '\', \''
    // . $caption
    // . '\', \''
    // . $type
    // . '\', \''
    // . $elements
    // . '\', \''
    // . $default_text
    // . '\', \''
    // . $display
    // . '\', \''
    // . $params
    // . '\', \''
    // . $rank
    // . '\', \''
    // . $locked
    // . '\', '
    // . $categoryid
    // . ');';
    // $rs = $modx->db->query( $sql );      
    // if( ! $rs )
    // {
      // return FALSE;
    // }
    // else
    // {	
      // // get the id
      // if( ! $newid = mysql_insert_id() )
      // {
        // exit;
      // }			
    // }
  // }
// }

?>