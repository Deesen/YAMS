<?php
/**
 * The YAMS snippet call
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */

require_once( dirname( __FILE__ ) . '/class/yams.class.inc.php' );

$yams = YAMS::GetInstance();

if ( !isset( $get ) )
{
  return '';
}

if ( !isset( $from ) )
{
  $from = NULL;
}

if ( !isset( $docid ) )
{
  $docid = NULL;
}

if ( ! isset( $beforetpl ) )
{
  $beforetpl = NULL;
}

if ( ! isset( $repeattpl ) )
{
  $repeattpl = NULL;
}

if ( ! isset( $currenttpl ) )
{
  $currenttpl = NULL;
}

if ( ! isset( $aftertpl ) )
{
  $aftertpl = NULL;
}
// error_log( 'docid: ' . $docid );

echo $yams->Snippet(
  $get
  , $from
  , $docid
  , $beforetpl
  , $repeattpl
  , $currenttpl
  , $aftertpl
);
?>