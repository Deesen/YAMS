<?php
/**
 * The YAMS plugin code
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

$evt = &$modx->Event;

$yams = YAMS::GetInstance();

switch ( $evt->name )
{
case 'OnPageNotFound':
case 'OnWebPageInit':
  $docIdFoundByYAMS = TRUE;

  // If the site is offline and we have arrived here
  // then we might need to display the site unavailable page...
  if ( !$modx->checkSiteStatus() && $this->config['site_unavailable_page'] )
  {
    // setup offline page document settings
    $this->documentMethod= "id";
    $this->documentIdentifier= $this->config['site_unavailable_page'];
  }
  else
  {

    // If a user
    // tries to access an url via index.php?id=...
    // then let them view the page. Don't do any redirection.
    if (
        ! isset( $_GET['q'] ) || $_GET['q'] == 'index.php'
      )
    {
      if ( ! isset( $_GET['id'] ) )
      {
        $langId = $yams->DetermineCurrentLangId();
        $docId = $modx->config['site_start'];
        $docIdFoundByYAMS = FALSE;
      }
      else
      {
        if ( ctype_digit( strval( $_GET['id'] ) ) )
        {
          $docIdFoundByYAMS = FALSE;
          $docId = $_GET['id'];
          $langId = $yams->DetermineCurrentLangId();
        }
        else
        {
          $docId = NULL;
          $langId = NULL;
        }
      }

  //    // If a valid docId has not been found, it will have been set to null
  //    // which will trigger MODx to generate a page not found event.
  //    $modx->documentMethod = 'id';
  //    if ( ! is_null( $docId ) )
  //    {
  //      $modx->documentIdentifier = $docId;
  //    }
    }
    else
    {

      if ( $yams->ActiveURLsAreIdentical()
        && $yams->GetUseMultilingualAliases()
        && $yams->GetMultilingualAliasesAreUnique()
      )
      {
        // Determine the docId and langId from the alias...
        // The currentLangId is set if a valid document is found.
        $docId = $yams->GetDocumentIdentifierUnique(
          $_GET['q']
          , $langId
          );
      }
      else
      {
        // Determine the langId from the server and root name
        $langId = $yams->DetermineCurrentLangId();
        // Determine the docId from the alias, given the language
        $docId = $yams->GetDocumentIdentifier(
          $_GET['q']
          , $langId
          );
      }

    }

    if ( is_null( $docId ) )
    {
      // If no docId was found...
      // but a standard MODx url matches... then use that.
      // This is for compatibility with standard MODx resources
      if ( $yams->IsValidId( $modx->documentIdentifier ) )
      {
        $docIdFoundByYAMS = FALSE;
        $docId = $modx->documentIdentifier;
      }
    }

    // If a valid docId has not been found, it will have been set to null
    // which will trigger MODx to generate a page not found event.
    $modx->documentMethod = 'id';
    $modx->documentIdentifier = $docId;
  }

  // Redirect if necessary
  $template = NULL;
  // should probably set a cookie here to protect against recursion...
  if ( $yams->Redirect( $docId, $template, $docIdFoundByYAMS ) )
  {
    exit();
    break;
  }

  // If a valid docId has been found, set the language determined...
  if ( ctype_digit( strval( $docId ) ) )
  {
    $yams->SetCurrentLangId( $langId, $docId );
    
    // If we are in a page not found event, but have succeeded in finding
    // a page, then forward to that page...
    if ( $evt->name == 'OnPageNotFound' )
    {
      $this->sendForward( $docId, 'HTTP/1.1 200 OK');
      break;
    }
  }

  // If we have got here and docId is NULL, then this will generate a page
  // not found event... Let MODx do its standard forwarding. This will automatically
  // get displayed in the current language.
  break;

case 'OnLoadWebPageCache':
  // Need to increment the YAMS counter so the new construct numbers
  // don't interfere with the ones already embedded din the document
  // Just set it to a large enough number.
  $yams->SetYamsCounter( 50000 );
  $yams->SetFromCache( TRUE );
  // Fall through...
  // break;
case 'OnLoadWebDocument':
  // error_log('OnLoadWebDocument');
  $docId = $modx->documentObject['id'];
  $template = $modx->documentObject['template'];

  $isMultilingualDocument =
    $yams->IsMultilingualDocument(
      $docId
//      , $template
    );
    
  $yams->InitialiseParser( $isMultilingualDocument );

  $yams->SelectOneLangFromCache( $modx->documentContent );
  // Parse YAMS markup
  do {
    $finished = $yams->PreParse(
      $modx->documentContent
      , $docId
      , $template
      , $isMultilingualDocument
      );
  } while ( ! $finished );

  break;
case 'OnParseDocument':
  // error_log('OnParseDocument');
  $docId = $modx->documentObject['id'];
  $template = $modx->documentObject['template'];
  
  $isMultilingualDocument =
    $yams->IsMultilingualDocument(
      $docId
//      , $template
    );
    
  $yams->InitialiseParser( $isMultilingualDocument );
  
  do {
    $finished = $yams->PreParse(
      $modx->documentOutput
      , $docId
      , $template
      , $isMultilingualDocument
      );
  } while ( ! $finished );
  
  break;
case 'OnWebPagePrerender':
  // error_log('OnWebPagePrerender');
  $docId = $modx->documentObject['id'];
  $template = $modx->documentObject['template'];

  $isMultilingualDocument =
    $yams->IsMultilingualDocument(
      $docId
//      , $template
    );

  $yams->InitialiseParser( $isMultilingualDocument );

  if ( ! $yams->PostParse(
        $isMultilingualDocument
        , $modx->documentOutput
        , NULL
        , FALSE
      ) )
  {
    return;
  }
  break;
case 'OnBeforeDocFormSave':

  global $template;
  global $pagetitle;
  global $tmplvars;
  global $alias;
  global $type;
  global $friendly_urls;
  global $automatic_alias;
  global $allow_duplicate_alias;
  global $use_alias_path;
  global $actionToTake;
  global $_lang;

  $docId = $evt->params['id'];

  $isMultilingualDocument =
    $yams->IsMultilingualDocument( $docId )
    || $yams->IsMultilingualTemplate( $template );

//  $docAlias = $alias;
//  if ( $docAlias == '' )
//  {
//    $docAlias = 'doc' . $docId;
//  }
//
  while ( $isMultilingualDocument )
  {
    foreach ( $yams->GetActiveLangIds() as $langId )
    {
      // Update pagetitles...
      $tvName = 'pagetitle_' . $langId;
      $tvId = YAMSGetTempVarId( $tvName );
      if ( $tvId === FALSE )
      {
        continue;
      }

      $tvIdentifier =
        YAMSTVDataToMMName( $tvName, $tvId, '' );
      if ( ! array_key_exists( $tvIdentifier, $tmplvars ) )
      {
        if ( ! array_key_exists( $tvName, $tmplvars ) )
        {
          continue;
        }
        else
        {
          // The pagetitle has been marked for deletion
          // because it is empty.
          // Undo that...
          unset( $tmplvars[ $tvName ] );
          // Set it's value to an empty string for the moment...
          $tmplvars[ $tvIdentifier ] = array(
            $tvIdentifier
            , ''
          );
        }
      }
      if ( $tmplvars[ $tvIdentifier ][1] == '' )
      {
        // Use the untitled text...
        if ( $type == 'reference' )
        {
          $tmplvars[ $tvIdentifier ][1] = $yams->GetMODxLangText( 'untitled_weblink', $langId );
        }
        else
        {
          $tmplvars[ $tvIdentifier ][1] = $yams->GetMODxLangText( 'untitled_document', $langId );
        }
      }
      $langPagetitle = $tmplvars[ $tvIdentifier ][1];
      
      // Do pagetitle synchronisation
      if (
        $langId == $yams->GetDefaultLangId()
        && $yams->GetSynchronisePagetitle() )
      {
        $pagetitle = $modx->db->escape( $langPagetitle );
      }      

      // Do menutitle synchronisation
      $tvName = 'menutitle_' . $langId;
      $tvId = YAMSGetTempVarId( $tvName );
      if ( $tvId === FALSE )
      {
        continue;
      }

      $tvIdentifier =
        YAMSTVDataToMMName( $tvName, $tvId, '' );
      if ( ! array_key_exists( $tvIdentifier, $tmplvars ) )
      {
        if ( ! array_key_exists( $tvName, $tmplvars ) )
        {
          continue;
        }
        else
        {
          // The menutitle has been marked for deletion
          // because it is empty.
          // Undo that...
          unset( $tmplvars[ $tvName ] );
          // Set it's value to an empty string for the moment...
          $tmplvars[ $tvIdentifier ] = array(
            $tvIdentifier
            , ''
          );
        }
      }
      if ( $tmplvars[ $tvIdentifier ][1] == '' )
      {
        // Use the pagetitle...
        $tmplvars[ $tvIdentifier ][1] = $langPagetitle;
      }
      // $langMenutitle = $tmplvars[ $tvIdentifier ][1];
      
      // Get the alias...
      $tvName = 'alias_' . $langId;
      $tvId = YAMSGetTempVarId( $tvName );
      if ( $tvId === FALSE )
      {
        continue;
      }
      $tvIdentifier = YAMSTVDataToMMName( $tvName, $tvId, '' );
      if ( ! array_key_exists( $tvIdentifier, $tmplvars ) )
      {
        if ( ! array_key_exists( $tvName, $tmplvars ) )
        {
          continue;
        }
        else
        {
          // The alias has been marked for deletion because it is empty
          // Undo that...
          unset( $tmplvars[ $tvName ] );
          // Set it's value to an empty string for the moment...
          $tmplvars[ $tvIdentifier ] = array (
            $tvIdentifier,
            ''
          );
        }
      }

      if ( $friendly_urls )
      {

        // Do alias auto-completion
        if ( $automatic_alias )
        {
          // Check if the alias is empty...
          if ( $tmplvars[ $tvIdentifier ][1] == '' )
          {
            // Construct an alias from the pagetitle...
            $langAlias = html_entity_decode(
              strip_tags( $langPagetitle )
              , ENT_QUOTES
              , $modx->config['modx_charset']
              );
            $tmplvars[ $tvIdentifier ][1] = $langAlias;
          }

        }

        // Put the alias through stripAlias...
        if ( $yams->GetUseStripAlias() )
        {
          if ( method_exists( $modx, 'stripAlias' ) )
          {
            $tmplvars[ $tvIdentifier ][1]
              = $modx->stripAlias( $tmplvars[ $tvIdentifier ][1] );
          }
          elseif ( function_exists( 'stripAlias' ) )
          {
            $tmplvars[ $tvIdentifier ][1]
              = stripAlias( $tmplvars[ $tvIdentifier ][1] );
          }
        }

        if ( $automatic_alias )
        {
          // If the alias is now empty... give it a safe value...
          if ( $tmplvars[ $tvIdentifier ][1] == '' )
          {
            if ( $yams->GetUseUniqueMultilingualAliases() )
            {
              $tmplvars[ $tvIdentifier ][1] = $langId . '-' . $docId;
            }
            else
            {
              $tmplvars[ $tvIdentifier ][1] = $docId;
            }
          }
        }

        // Check for duplicate aliases...
        if ( !$allow_duplicate_alias )
        {
          // Check whether the alias is unique... (for this language)
          $duplicateDocId = $yams->GetDuplicateAliasDocIdMono(
            $tmplvars[ $tvIdentifier ][1]
            , $docId
            , $langId
          );
          if ( ! ( $duplicateDocId === FALSE ) && $duplicateDocId > 0 )
          {
            if ( $automatic_alias )
            {
              // Make the alias unique
              $counter = 0;
              // Now try the standard MODx technique
              do {
                $counter++;
                $tempAlias = $tmplvars[ $tvIdentifier ][1] . '-' . $counter;
                $duplicateDocId = $yams->GetDuplicateAliasDocIdMono(
                  $tempAlias
                  , $docId
                  , $langId
                  );
              } while( ! ( $duplicateDocId === FALSE ) && $duplicateDocId > 0 );

              $tmplvars[ $tvIdentifier ][1] = $tempAlias;
            }
            else
            {
              if ( $actionToTake == 'edit' )
              {
                $modx->manager->saveFormValues(27);
                $url = 'index.php?a=27&id=' . $docId;
                include_once 'header.inc.php';
                $modx->webAlert(
                  sprintf(
                    $_lang['duplicate_alias_found']
                    , $duplicateDocId . ' (' . $langId . ')'
                    , $tmplvars[ $tvIdentifier ][1]
                  )
                  , $url);
                include_once 'footer.inc.php';
                exit;
              } else {
                $modx->manager->saveFormValues(4);
                $url = 'index.php?a=4';
                include_once 'header.inc.php';
                $modx->webAlert(
                  sprintf(
                    $_lang['duplicate_alias_found']
                    , $duplicateDocId . ' (' . $langId . ')'
                    , $tmplvars[ $tvIdentifier ][1]
                  )
                  , $url);
                include_once 'footer.inc.php';
                exit;
              }
            }
          }
        }

        // Now do checks for unique aliases...
        if ( $yams->GetUseUniqueMultilingualAliases() )
        {
          // Check whether the alias is unique... (for this language)
          $duplicateDocId = $yams->GetDuplicateAliasDocIdMulti(
            $tmplvars[ $tvIdentifier ][1]
            , $docId
            , $langId
          );
          if ( ! ( $duplicateDocId === FALSE ) && $duplicateDocId > 0 )
          {
            if ( $automatic_alias )
            {
              // Make the alias unique
              $counter = '';
              // Now try the standard MODx technique
              do {
                $tempAlias = $tmplvars[ $tvIdentifier ][1] . '-' . $langId . $counter;
                $duplicateDocId = $yams->GetDuplicateAliasDocIdMulti(
                  $tempAlias
                  , $docId
                  , $langId
                  );
                if ( $counter == '' )
                {
                  $counter = 0;
                }
                $counter++;
              } while( ! ( $duplicateDocId === FALSE ) && $duplicateDocId > 0 );
              $tmplvars[ $tvIdentifier ][1] = $tempAlias;
            }
            else
            {
              if ( $actionToTake == 'edit' )
              {
                $modx->manager->saveFormValues(27);
                $url = 'index.php?a=27&id=' . $docId;
                include_once 'header.inc.php';
                $modx->webAlert(
                  sprintf(
                    $_lang['duplicate_alias_found']
                    , $duplicateDocId
                    , $tmplvars[ $tvIdentifier ][1]
                  )
                  , $url);
                include_once 'footer.inc.php';
                exit;
              } else {
                $modx->manager->saveFormValues(4);
                $url = 'index.php?a=4';
                include_once 'header.inc.php';
                $modx->webAlert(
                  sprintf(
                    $_lang['duplicate_alias_found']
                    , $duplicateDocId
                    , $tmplvars[ $tvIdentifier ][1]
                  )
                  , $url);
                include_once 'footer.inc.php';
                exit;
              }
            }
          }
        }
      }
      else
      {
        // Put the alias through stripAlias...
        if ( $yams->GetUseStripAlias() )
        {
          if ( method_exists( $modx, 'stripAlias' ) )
          {
            $tmplvars[ $tvIdentifier ][1]
              = $modx->stripAlias( $tmplvars[ $tvIdentifier ][1] );
          }
          elseif ( function_exists( 'stripAlias' ) )
          {
            $tmplvars[ $tvIdentifier ][1]
              = stripAlias( $tmplvars[ $tvIdentifier ][1] );
          }
        }
      }
    }

    break;
  }
  break;
default:
  return;
}

?>