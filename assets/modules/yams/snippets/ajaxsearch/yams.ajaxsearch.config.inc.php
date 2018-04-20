<?php

// This file allows you to have AS results obtained by AJAX appear in the
// correct language when using YAMS.
//
// To use, replace occurrences of document variable placeholders like
// [+as.pagetitle+], [+as.description+] etc.
// with
// 
// [[YAMS? &get=`data` &from=`pagetitle` &docid=`[+as.id+]`]]
// [[YAMS? &get=`data` &from=`description` &docid=`[+as.id+]`]]
// etc.
//
// in your AS ajaxResults.tpl.html templates. Then add
//
// require_once( $modx->config['base_path'] . 'assets/modules/yams/snippets/ajaxsearch/yams.ajaxsearch.config.inc.php' );
//
// to your ajaxsearch config file. By default this is at assets/snippets/ajaxSearch/configs/default.config.php
//
// Finally, either add &stripOutput=`asParseYAMS` to your AS snippet call, or
// if you are already using a custom stripOutput function, make
//
// $results = asParseYAMS( $results );
// 
// the first line.

require_once( $modx->config['base_path'] . 'assets/modules/yams/class/yams.class.inc.php' );
if ( ! function_exists( 'asParseYAMS' ) )
{
  function asParseYAMS( $results )
  {
    global $modx;
    
    $yams = YAMS::GetInstance();

    $docId = $modx->documentObject['id'];
    
    $isMultilingualDocument =
      $yams->IsMultilingualDocument( $docId );
      
    $yams->InitialiseParser( $isMultilingualDocument );
    
    do {
      $finished = $yams->PreParse(
        $results
        , $docId
        , $template
        , $isMultilingualDocument
        );
    } while ( ! $finished );
    
    $success = $yams->PostParse(
          $isMultilingualDocument
          , $results
          , NULL
          , FALSE
        );
    if ( ! $success )
    {
      return '';
    }
    
    return $results;
  }
}
?>