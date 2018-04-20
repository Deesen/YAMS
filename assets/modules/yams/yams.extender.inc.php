<?php
/**
 * A Ditto extendender for use with YAMS
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */

// require_once( dirname( __FILE__ ) . '/class/yams.class.inc.php' );

// ---------------------------------------------------
// Group: Placeholders
// Define the values of custom placeholders for access in the tpl like so [+phname+]
// ---------------------------------------------------

// Variable: $placeholders['example']
// Add the placeholder example to the custom placeholders list 
// with the source pagetitle in both display and backend using the 
// exampleFunction callback and pagetitle as the field for QuickEdit.
// If you only needed the placeholder in the frontent you would just
// use "pagetitle"  as the first value of the array. If the callback 
// was in a class use the array($initialized_class,"member") method.

$placeholders['title'] = array(
    array( '*' )
    , 'yamsChooseLangPageTitle'
  );
$placeholders['pagetitle'] = array(
    array( '*' )
    , 'yamsChooseLangPageTitle'
  );
$placeholders['longtitle'] = array(
    array( '*' )
    , 'yamsChooseLangLongTitle'
  );
$placeholders['description'] = array(
    array( '*' )
    , 'yamsChooseLangDescription'
  );
$placeholders['introtext'] = array(
    array( '*' )
    , 'yamsChooseLangIntroText'
  );
$placeholders['menutitle'] = array(
    array( '*' )
    , 'yamsChooseLangMenuTitle'
  );
$placeholders['content'] = array(
    array( '*' )
    , 'yamsChooseLangContent'
  );

$GLOBALS['ditto_object'] = $ditto;

if ( ! function_exists( 'yamsChooseLangPageTitle' ) )
{
  function yamsChooseLangPageTitle( $resource )
  {
    global $ditto_object;

    $yams = YAMS::GetInstance();

    if ( ! $yams->IsMultilingualDocument(
          $resource['id']
//          , $resource['template']
          ) )
    {
      return $resource['pagetitle'];
    }
    $output = $yams->MultiLangExpand(
        'data'
        , 'pagetitle'
        , $resource['id']
      );
    return $output;
  }
}

if ( ! function_exists( 'yamsChooseLangLongTitle' ) )
{
  function yamsChooseLangLongTitle( $resource )
  {
    global $ditto_object;

    $yams = YAMS::GetInstance();

    if ( ! $yams->IsMultilingualDocument(
          $resource['id']
//          , $resource['template']
          ) )
    {
      return $resource['longtitle'];
    }
    $output = $yams->MultiLangExpand(
        'data'
        , 'longtitle'
        , $resource['id']
      );
    return $output;
  }
}

if ( ! function_exists( 'yamsChooseLangDescription' ) )
{
  function yamsChooseLangDescription( $resource )
  {
    global $ditto_object;

    $yams = YAMS::GetInstance();

    if ( ! $yams->IsMultilingualDocument(
          $resource['id']
//          , $resource['template']
          ) )
    {
      return $resource['description'];
    }
    $output = $yams->MultiLangExpand(
        'data'
        , 'description'
        , $resource['id']
      );
    return $output;
  }
}

if ( ! function_exists( 'yamsChooseLangIntroText' ) )
{
  function yamsChooseLangIntroText( $resource )
  {
    global $ditto_object;

    $yams = YAMS::GetInstance();

    if ( ! $yams->IsMultilingualDocument(
          $resource['id']
//          , $resource['template']
          ) )
    {
      return $resource['introtext'];
    }
    $output = $yams->MultiLangExpand(
        'data'
        , 'introtext'
        , $resource['id']
      );
    return $output;
  }
}

if ( ! function_exists( 'yamsChooseLangMenuTitle' ) )
{
  function yamsChooseLangMenuTitle( $resource )
  {
    global $ditto_object;

    $yams = YAMS::GetInstance();

    if ( ! $yams->IsMultilingualDocument(
          $resource['id']
//          , $resource['template']
          ) )
    {
      return $resource['menutitle'];
    }
    $output = $yams->MultiLangExpand(
        'data'
        , 'menutitle'
        , $resource['id']
      );
    return $output;
  }
}

if ( ! function_exists( 'yamsChooseLangContent' ) )
{
  function yamsChooseLangContent( $resource )
  {
    global $ditto_object;

    $yams = YAMS::GetInstance();

    if ( ! $yams->IsMultilingualDocument(
          $resource['id']
//          , $resource['template']
          ) )
    {
      return $resource['content'];
    }
    $output = $yams->MultiLangExpand(
        'data'
        , 'content'
        , $resource['id']
      );
    return $output;
  }
}

?>