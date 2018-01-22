<?php
/**
 * YAMS
 *
 * Yet Another Multilingual Solution
 *
 * @category 	snippet
 * @version 	1.2.0 RC6
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