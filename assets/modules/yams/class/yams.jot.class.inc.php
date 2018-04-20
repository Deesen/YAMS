<?php
/**
 * A class which extends Jot to provide multilingual aliases
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */
 
require_once( dirname( __FILE__ ) . '/yams.class.inc.php' );
require_once( dirname( __FILE__ ) . '/../../../snippets/jot/jot.class.inc.php' );

class CJotYAMS extends CJot {	
	
	// MODx makeUrl enhanced: preserves querystring.
	function preserveUrl($docid = '', $alias = '', $array_values = array(), $suffix = false) {
		global $modx;
    $yams = YAMS::GetInstance();
    
		$array_get = $_GET;
		$urlstring = array();
		
		unset($array_get["id"]);
		unset($array_get["q"]);
    // YAMS START
    unset($array_get[ $yams->GetLangQueryParam() ]);
    // YAMS END
		
		$array_url = array_merge($array_get, $array_values);
		foreach ($array_url as $name => $value) {
			if (!is_null($value)) {
			  $urlstring[] = $name . '=' . urlencode($value);
			}
		}
		
		$url = join('&',$urlstring);
    // YAMS START
    if ( $url != '' )
    {
      $url = '?' . $url;
    }
    // YAMS END
		if ($suffix) {
			if (empty($url)) { $url = "?"; }
			 else { $url .= "&"; }
		}
    
		// YAMS START
    // return $modx->makeUrl($docid, $alias, $url);
		return '(yams_doc:' . $docid . ')' . $url;
    // YAMS END
    
	}
	
}
?>