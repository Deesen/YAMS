<?php
/**
 * The main YAMS service. Manages the config file, document parsing,
 * page redirection etc.
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @author Original multilingual alias code supplied by
 *         mgbowman (http://modxcms.com/forums/index.php?action=profile;u=21916)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @copyright For code marked YAMS UX Matthew Bowman 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */

define(
  'YAMS_DOC_LIMIT'
  , 50 );
define(
  'YAMS_RE_SERVER_NAME'
  , '(|(?:[a-z0-9]\.|[a-z0-9][-a-z0-9]{0,61}[a-z0-9]\.)*(?:com|edu|gov|int|mil|net|org|biz|info|aero|localhost|[a-z][a-z]|[0-9]{1,3})(\:[0-9]+)?)' );

// if ( ! function_exists('pcre_error_decode') )
// {
 // function pcre_error_decode()
 // {
   // switch ( preg_last_error() )
   // {
// //      case PREG_PATTERN_ORDER:
// //      return 'Orders results so that $matches[0] is an array of full pattern matches, $matches[1] is an array of strings matched by the first parenthesized subpattern, and so on. This flag is only used with preg_match_all().';
// //      break;
// //      case PREG_SET_ORDER:
// //      return 'Orders results so that $matches[0] is an array of first set of matches, $matches[1] is an array of second set of matches, and so on. This flag is only used with preg_match_all().';
// //      break;
// //      case PREG_OFFSET_CAPTURE:
// //      return 'See the description of PREG_SPLIT_OFFSET_CAPTURE. This flag is available since PHP 4.3.0.';
// //      break;
// //      case PREG_SPLIT_NO_EMPTY:
// //      return 'This flag tells preg_split() to return only non-empty pieces.';
// //      break;
// //      case PREG_SPLIT_DELIM_CAPTURE:
// //      return 'This flag tells preg_split() to capture parenthesized expression in the delimiter pattern as well. This flag is available since PHP 4.0.5.';
// //      break;
// //      case PREG_SPLIT_OFFSET_CAPTURE:
// //      return 'If this flag is set, for every occurring match the appendant string offset will also be returned. Note that this changes the return values in an array where every element is an array consisting of the matched string at offset 0 and its string offset within subject at offset 1. This flag is available since PHP 4.3.0 and is only used for preg_split().';
// //      break;
     // case PREG_NO_ERROR:
     // // do not print in this case
     // return 'Returned by preg_last_error() if there were no errors. Available since PHP 5.2.0.';
     // break;
     // case PREG_INTERNAL_ERROR:
     // return 'Returned by preg_last_error() if there was an internal PCRE error. Available since PHP 5.2.0.';
     // break;
     // case PREG_BACKTRACK_LIMIT_ERROR:
     // return 'Returned by preg_last_error() if backtrack limit was exhausted. Available since PHP 5.2.0.';
     // break;
     // case PREG_RECURSION_LIMIT_ERROR:
     // return 'Returned by preg_last_error() if recursion limit was exhausted. Available since PHP 5.2.0.';
     // break;
     // case PREG_BAD_UTF8_ERROR:
     // return 'Returned by preg_last_error() if the last error was caused by malformed UTF-8 data (only when running a regex in UTF-8 mode). Available since PHP 5.2.0.';
     // break;
     // case PREG_BAD_UTF8_OFFSET_ERROR:
     // return 'Returned by preg_last_error() if the offset didn\'t correspond to the begin of a valid UTF-8 code point (only when running a regex in UTF-8 mode). Available since PHP 5.3.0.';
     // break;
// //      case PCRE_VERSION:
// //      return 'PCRE version and release date (e.g. \'7.0 18-Dec-2006\'). Available since PHP 5.2.4.';
// //      break;
     // default:
       // return 'PCRE unrecognised error';
   // }
 // }
// }

if ( ! class_exists( 'YAMS' ) )
{
  class YAMS
  {

    // --
    // -- Public Stuff
    // --

    public function GetVersion()
    {
      return '1.1.9';
    }

    public function Escape( $string )
    {
      // Escapes a string for insertion as content in html...
      return htmlspecialchars(
        $string
        , ENT_QUOTES
        , $this->itsMODx->config['modx_charset']
        );
    }

    public function Clean( $string )
    {
      // Cleans a string and escapes it for insertion as content in html...
      return $this->Escape( strip_tags( $string ) );
    }

    public function GetDuplicateAliasDocIdMono( $alias, $docId, $langId )
    {
      // Given a new alias for lang langId of doc docId, check whether it is
      // a unique alias for that language only....
      // This is a repeat of what MODx does for normal aliases, but for
      // a specific language
      //
      // If friendly alias paths are being used, then this only applies
      // to children of the document parent...

      // Return the id of the first duplicate document if one exists,
      // or FALSE otherwise
      if ( ! array_key_exists( $langId, $this->itsDocAliases ) )
      {
        // Could return NULL here and do some error analysis...
        return FALSE;
      }      
      if ( $this->itsMODx->config[ 'use_alias_path' ] )
      {
        // Get all documents with the same parent...
        if ( !array_key_exists( $docId, $this->itsDocParentIds ) )
        {
          return FALSE;
        }
        $parentId = $this->itsDocParentIds[ $docId ];
        // Get all the sibling (children with the same parent...)
        $siblings = array_intersect( $this->itsDocParentIds, array( $parentId ) );
        unset( $siblings[ $docId ] );
        // Get the aliases of the siblings...
        $aliases = array_intersect_key( $this->itsDocAliases[ $langId ], $siblings );
        return array_search( $alias, $aliases );
      }
      else
      {
        $aliases = $this->itsDocAliases[ $langId ];
        unset( $aliases[ $docId ] );
        return array_search( $alias, $aliases );
      }
    }

    public function GetDuplicateAliasDocIdMulti( $alias, $docId, $langId )
    {
      // Given a new alias for lang langId of doc docId, check whether it
      // forms a unique URL ...
      //
      // This is a like what MODx does for normal aliases, but takes into
      // account multilingual variants...
      //
      // If friendly alias paths are being used, then this only applies
      // to children of the document parent...

      // Return the doc id of the first duplicate alias if one exists,
      // or FALSE otherwise

      if ( ! array_key_exists( $langId, $this->itsDocAliases ) )
      {
        // Could return NULL here and do some error analysis...
        return FALSE;
      }
      if ( $this->itsMODx->config[ 'use_alias_path' ] )
      {
        // Get all documents with the same parent...
        if ( !array_key_exists( $docId, $this->itsDocParentIds ) )
        {
          return FALSE;
        }
        $parentId = $this->itsDocParentIds[ $docId ];
        // Get all the sibling (children with the same parent...)
        $siblings = array_intersect( $this->itsDocParentIds, array( $parentId ) );
        // unset( $siblings[ $docId ] );
        // Get the aliases of the siblings...
        foreach ( $this->itsActiveLangIds as $thisLangId )
        {
          $aliases = array_intersect_key( $this->itsDocAliases[ $thisLangId ], $siblings );
          if ( $thisLangId == $langId )
          {
            unset( $aliases[ $docId ] );
          }
          $duplicateDocId = array_search( $alias, $aliases );
          if ( ! ( $duplicateDocId === FALSE ) )
          {
            return $duplicateDocId;
          }
        }
        return FALSE;
      }
      else
      {
        foreach ( $this->itsActiveLangIds as $thisLangId )
        {
          $aliases = $this->itsDocAliases[ $thisLangId ];
          if ( $thisLangId == $langId )
          {
            unset( $aliases[ $docId ] );
          }
          $duplicateDocId = array_search( $alias, $aliases );
          if ( ! ( $duplicateDocId === FALSE ) )
          {
            return $duplicateDocId;
          }
        }
        return FALSE;
      }
    }

    public function SetYamsCounter( $num )
    {
      if ( ! $this->IsValidId( $num ) )
      {
        return FALSE;
      }
      $this->itsYamsCounter = $num;
      return TRUE;
    }
    
    public function ConstructURL(
      $langId = NULL
      , $docId = NULL
      // , $includeRequestURI = TRUE
      , $includeRootName = TRUE
      , $includeTrailingSlash = TRUE
      , $includeVirtualPath = TRUE
      , $includeGetParams = TRUE
      , $includeQueryParam = TRUE
      , $stripChangeLangQueryParam = FALSE
      , $isHTMLOutput = TRUE
      )
    {
      if ( ! $this->IsActiveLangId( $langId ) && ! is_null( $langId ) )
      {
        return '';
      }

      // Get the servername and port
      $isHTTPS = $this->IsHTTPS();
      if ( $isHTTPS )
      {
        $protocol = 'https://';
      }
      else
      {
        $protocol = 'http://';
      }
//      $stripPort =
//        ( $_SERVER['SERVER_PORT'] != 80 )
//        && ( ! $isHTTPS );
      $serverNameAndPort = $this->GetActiveServerName(
        $langId
//        , $stripPort
        );

      // Get the MODx subdirectory
      $modxSubdirectory = $this->GetMODxSubdirectory( false, true );

      // Get the root name
      $rootName = '';
      if ( $includeRootName )
      {
        $rootName = $this->GetActiveRootName( $langId );
        if ( $rootName != '' )
        {
          $rootName = '/' . $rootName;
        }
      }

      // Get the trailing slash, if required.
      $trailingSlash = '/';
      if ( $includeVirtualPath || ! $includeTrailingSlash )
      {
        $trailingSlash = '';
      }

      $decodedQueryParams = array();
      $virtualPath = '';

      if ( $includeVirtualPath )
      {
        // Get the document virtual path
        $virtualPath = $this->GetDocumentAlias(
          $docId
          , $langId
          , $this->itsShowSiteStartAlias
          );
        if ( $virtualPath === FALSE )
        {
          return '';
        }
        $virtualPath = '/' . $virtualPath;
        
      }

      if ( $includeGetParams )
      {
        foreach ( $_GET as $name => $value )
        {
          $decodedQueryParams[ $this->UrlDecode( $name ) ] =
            $this->UrlDecode( $value );
        }

        if ( ! $this->IsValidId( $docId ) )
        {
          return '';
        }

        if ( array_key_exists( $this->itsLangQueryParam, $decodedQueryParams ) )
        {
          unset( $decodedQueryParams[ $this->itsLangQueryParam ] );
        }
        if ( $stripChangeLangQueryParam )
        {
          if ( array_key_exists( $this->itsChangeLangQueryParam, $decodedQueryParams ) )
          {
            unset( $decodedQueryParams[ $this->itsChangeLangQueryParam ] );
          }
        }
      }

      if ( $includeQueryParam && $this->itsUseLanguageQueryParam )
      {
        // $decodedQueryParams[ $this->itsLangQueryParam ] = $langId;
        $decodedQueryParams =
          array( $this->itsLangQueryParam => $langId )
          + $decodedQueryParams;
      }
        
      if ( array_key_exists( 'q', $decodedQueryParams ) )
      {
        unset( $decodedQueryParams[ 'q' ] );
      }
      if ( array_key_exists( 'id', $decodedQueryParams ) )
      {
        unset( $decodedQueryParams[ 'id' ] );
      }
      
      if ( $includeVirtualPath )
      {
        if ( ! $this->itsMODx->config['friendly_urls'] )
        {
          $decodedQueryParams =
            array( 'id' => $docId )
            + $decodedQueryParams;
          // $decodedQueryParams[ 'id' ] = $docId;
        }
      }
      
      $requestURI = '';
      if ( count( $decodedQueryParams ) > 0 )
      {
        $encodedQueryParams = array();
        foreach ( $decodedQueryParams as $name => $value )
        {
          $encodedQueryParams[] =
            $this->UrlEncode( $name )
              . '='
              . $this->UrlEncode( $value );
        }
        unset( $decodedQueryParams );
        $querySeparator = $this->itsInputQuerySeparator;
        $requestURI =
          '?'
          . implode(
              $querySeparator
              , $encodedQueryParams
            );
      }
      
      $url =
        $protocol
        . $serverNameAndPort
        . $modxSubdirectory
        . $rootName
        . $trailingSlash
        . $virtualPath
        . $requestURI;
      if ( $isHTMLOutput )
      {
        return $this->Escape( $url );
      }
      return $url;
    }

    public function GetDocVarNames()
    {
      // Gets an array of the the names of the document variables
      // managed by YAMS
      $docVarNames = $this->itsDocVarNames;
      if ( ! $this->itsUseMultilingualAliases )
      {
        // Remove the alias value, if it exists
        $docVarNames = array_values(
          array_diff(
            $docVarNames
            , array( 'alias' )
          )
        );
      }
      return $docVarNames;
    }

    public function GetDocVarType( $docVarName )
    {
      // Gets the type assigned to a given document variable
      if ( ! array_key_exists( $docVarName, $this->itsDocVarTypes ) )
      {
        return 'text';
      }
      return $this->itsDocVarTypes[ $docVarName ];
    }

    public function GetRolesAccessList( $langId )
    {
      if ( ! array_key_exists( $langId, $this->itsLangRolesAccessMap ) )
      {
        return '';
      }
      return $this->itsLangRolesAccessMap[ $langId ];
    }

    public function GetRolesNoAccessList( $langId )
    {
      if ( ! array_key_exists( $langId, $this->itsLangRolesAccessMap ) )
      {
        return '!';
      }
      $rolesList = $this->itsLangRolesAccessMap[ $langId ];
      if (
        preg_match(
          '/^\!/' . $this->itsEncodingModifier
          , $rolesList
        ) == 1 )
      {
        $rolesList = preg_replace(
          '/^\!/' . $this->itsEncodingModifier
          , ''
          , $rolesList );
      }
      else
      {
        $rolesList = '!' . $rolesList;
      }
      return $rolesList;
    }

    public function GetDocVarCaption( $docVarName, $langId )
    {

      switch ( $docVarName )
      {
      case 'pagetitle':
        $modxLangKey = 'document_title';
        break;
      case 'longtitle':
        $modxLangKey = 'long_title';
        break;
      case 'description':
        $modxLangKey = 'document_description';
        break;
      case 'introtext':
        $modxLangKey = 'document_summary';
        break;
      case 'alias':
        $modxLangKey = 'document_alias';
        break;
      case 'menutitle':
        $modxLangKey = 'document_opt_menu_title';
        break;
      case 'content':
        // For weblinks this will be wrong.. but well,
        // can't do much about that, except via a special
        // manager manager rule.
        $modxLangKey = 'document_content';
        break;
      default:
        return '';
      }

      return $this->GetMODxLangText( $modxLangKey, $langId );

    }

    public function GetMODxLangText( $langKey, $langId )
    {
      $modxLangName = $this->GetMODxLangName( $langId );
      if ( $modxLangName == '' )
      {
        return '';
      }
      
      // Gets the caption to use for a template variable in the
      // given MODx manager language
      $englishFilename =
        $this->itsMODx->config['base_path']
        . '/manager/includes/lang/english.inc.php';
      $specifiedFilename =
        $this->itsMODx->config['base_path']
        . '/manager/includes/lang/'
        . $modxLangName
        . '.inc.php';

      // Load english first.. This is the default...
      $_lang = array();
      include $englishFilename;

      // Load the requested language...
      if(
        $modxLangName != 'english'
        && file_exists( $specifiedFilename )
        )
      {
        include $specifiedFilename;
      }

      if ( array_key_exists( $langKey, $_lang ) )
      {
        return $_lang[ $langKey ];
      }

      return '';
      
    }

    public function GetEncodingModifier()
    {
      // The encoding modifier ('u' or '') to use with
      // preg functions
      return $this->itsEncodingModifier;
    }

    public function GetLangQueryParam()
    {
      // Gets the name of the query parameter to use
      // when the current language group id is being specified by
      // a query parameter
      return $this->itsLangQueryParam;
    }

    public function GetChangeLangQueryParam()
    {
      // Gets the name of the query parameter to used to specify a change
      // of language
      return $this->itsChangeLangQueryParam;
    }

    public function SetChangeLangQueryParam(
      $name
      , $save = TRUE )
    {
      // Sets the name of the query parameter to used to specify a change
      // of language
      if ( ! is_string( $name ) )
      {
        return FALSE;
      }
      if ( $name == $this->itsLangQueryParam )
      {
        return FALSE;
      }
      if ( $name == '' )
      {
        return FALSE;
      }
      if ( $name != $this->itsChangeLangQueryParam )
      {
        $this->itsChangeLangQueryParam = $name;
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function Reload()
    {
      // Reinitialises YAMS
      $this->Initialise();
    }

    public function SetLangQueryParam(
      $name
      , $save = TRUE )
    {
      // Sets the query parameter used to specify the current language group
      // id when in query param mode
      if ( ! is_string( $name ) )
      {
        return FALSE;
      }
      if ( $name == $this->itsChangeLangQueryParam )
      {
        return FALSE;
      }
      if ( $name == '' )
      {
        return FALSE;
      }
      if ( $name != $this->itsLangQueryParam )
      {
        $this->itsLangQueryParam = $name;
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function SetMODxSubdirectory(
      $subdir
      , $save = TRUE )
    {
      // Either an empty string
      // or string of the form sub1/sub2/sub3
      // ie; no starting or trailing slash
      if ( ! is_string( $subdir ) )
      {
        return FALSE;
      }
      if ( $subdir != strip_tags( $subdir ) )
      {
        return FALSE;
      }
      if ( preg_match(
          '/^(|[^\n\/]|[^\n\/][^\n]*[^\n\/])$/D'
            . $this->itsEncodingModifier
          , $subdir
          ) != 1 )
      {
        return FALSE;
      }
      if ( $subdir != $this->itsMODxSubdirectory )
      {
        $this->itsMODxSubdirectory = $subdir;
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function GetMODxSubdirectory(
      $trailingSlash = false
      , $leadingSlash = false
      , $encoded = true
      )
    {
      if ( $this->itsMODxSubdirectory == '' )
      {
        return '';
      }

      if ( $encoded )
      {
        $modxSubdirectoryArray =
          preg_split(
            '/\//' . $this->itsEncodingModifier
            , $this->itsMODxSubdirectory
            );
        foreach ( $modxSubdirectoryArray as &$part )
        {
          $part = $this->UrlEncode( $part );
        }
        $modxSubdirectory = implode( '/', $modxSubdirectoryArray );
        unset( $modxSubdirectoryArray );        
      }
      else
      {
        $modxSubdirectory = $this->itsMODxSubdirectory;
      }

      $leadingSlashSymbol = '';
      $trailingSlashSymbol = '';
      if ( $leadingSlash )
      {
        $leadingSlashSymbol = '/';
      }
      if ( $trailingSlash )
      {
        $trailingSlashSymbol = '/';
      }
      return
        $leadingSlashSymbol
        . $modxSubdirectory
        . $trailingSlashSymbol;
    }

    public function GetServerConfig()
    {
      $output = '';
      if ( ! $this->GetUseLanguageDependentServerNames() )
      {
        return $output;
      }
      $serverName = $this->GetServerName( $this->itsDefaultLangId );
      // The default language...
      $output .=
        'ServerName '
        . $serverName
        . PHP_EOL;
      $serverAliases = array();
      // All languages except the default language
      foreach ( $this->itsActiveLangIds as $langId )
      {
        if ( $langId == $this->itsDefaultLangId )
        {
          continue;
        }
        $serverAlias = $this->GetServerName( $langId );
        if ( $serverAlias == $serverName )
        {
          continue;
        }
        $serverAliases[] =
          'ServerAlias '
          . $serverAlias
          . PHP_EOL;
      }
      // The monolingual language
      $serverAlias = $this->GetServerName( NULL );
      if ( $serverAlias != $serverName )
      {
        $serverAliases[] =
          'ServerAlias '
          . $serverAlias
          . PHP_EOL;
      }
      $output .= implode('', array_unique( $serverAliases ) );
      return $output;
    }

    public function GetFriendlyURLConfig()
    {
      $inputQuerySeparator = ini_get( 'arg_separator.input' );
      if ( ! is_string( $inputQuerySeparator ) )
      {
        $inputQuerySeparator = '&';
      }
      else
      {
        $inputQuerySeparator =
          preg_replace(
            '/^(.)/'
              . $this->itsEncodingModifier
            , '\1'
            , $inputQuerySeparator
          );
      }
      $modxSubdirectory = $this->GetMODxSubdirectory( TRUE, FALSE, TRUE );

      $output =
        '# Friendly URLs' . PHP_EOL
        . 'RewriteEngine On' . PHP_EOL
        . 'RewriteBase /' . $modxSubdirectory . PHP_EOL
        . PHP_EOL
        . '# Fix Apache internal dummy connections from breaking [(site_url)] cache' . PHP_EOL
        . 'RewriteCond %{HTTP_USER_AGENT} ^.*internal\ dummy\ connection.*$ [NC]' . PHP_EOL
        . 'RewriteRule .* - [F,L]' . PHP_EOL
        . PHP_EOL
        . '# Exclude /assets and /manager directories from rewrite rules' . PHP_EOL
        . 'RewriteRule ^(manager|assets) - [L]' . PHP_EOL
        . PHP_EOL;

      if ( ! $this->GetUseLanguageQueryParam() )
      // if ( $this->itsMODx->config[ 'friendly_urls' ] )
      {
        $serverSettings = array();
        $serverSettingsOrder = array();
        $veryLargeInteger = 1000000;
        $counter = 0;
        $serverNameMode = $this->GetUseLanguageDependentServerNames();
        $rootNameMode = $this->GetUseLanguageDependentRootNames();
        $isHTTPS = $this->IsHTTPS();
        if ( $isHTTPS )
        {
          $protocol = 'https://';
        }
        else
        {
          $protocol = 'http://';
        }
        if ( $rootNameMode )
        {
          $output .=
            '# Redirect from mydomain.com/rootname to mydomain.com/rootname/' . PHP_EOL;
          // Multilingual pages
          foreach ( $this->itsActiveLangIds as $langId )
          {
            $rootName = $this->GetActiveRootName( $langId );
            if ( $rootName != '' )
            {
              $output .= 'RewriteRule ^' . $rootName . '$ ' . $rootName . '/ [R=301,L]' . PHP_EOL;
            }
          }
          $output .=
            PHP_EOL;
        }
        $output .=
          '# The Friendly URLs part' . PHP_EOL;
        if ( $serverNameMode || $rootNameMode )
        {
          // Multilingual pages
          foreach ( $this->itsActiveLangIds as $langId )
          {
            $counter++;
            $rootName = $this->GetRootName( $langId );
            $serverName = $this->GetServerName( $langId );
            
            if ( $rootName != '' )
            {
              $rootName = $rootName . '/';
            }
            $serverSettings[$counter] = '';
            $serverSettingsOrder[$counter] = $veryLargeInteger - strlen( $rootName );
            if ( $serverNameMode )
            {
              $serverName = $this->GetServerName( $langId );
              $serverSettings[$counter] .=
                'RewriteCond %{HTTP_HOST} ^'
                  . str_replace( '.', '\.', $serverName )
                  . '$'
                  . PHP_EOL;
            }
            $serverSettings[$counter] .=
              'RewriteCond %{REQUEST_FILENAME} !-f' . PHP_EOL
              . 'RewriteCond %{REQUEST_FILENAME} !-d' . PHP_EOL;
            $serverSettings[$counter] .=
              'RewriteRule ^'
                . $rootName
                . '(.*)$ '
                . 'index.php?q=$1'
//                . $inputQuerySeparator
//                . $this->itsLangQueryParam
//                . '='
//                . $langId
                . ' [L,QSA]'
                . PHP_EOL;
          }
        }
        
        // Monolingual pages
        $counter++;
        $rootName = $this->GetRootName( NULL );
        if ( $rootName != '' )
        {
          $rootName = $rootName . '/';
        }
        $serverSettings[$counter] = '';
        $serverSettingsOrder[$counter] = $veryLargeInteger - strlen( $rootName );
        if ( $serverNameMode )
        {
          $serverName = $this->GetServerName( NULL );
          $serverSettings[$counter] .=
            'RewriteCond %{HTTP_HOST} ^'
              . str_replace( '.', '\.', $serverName )
              . '$'
              . PHP_EOL;
        }
        $serverSettings[$counter] .=
          'RewriteCond %{REQUEST_FILENAME} !-f' . PHP_EOL
          . 'RewriteCond %{REQUEST_FILENAME} !-d' . PHP_EOL;
        $serverSettings[$counter] .=
          'RewriteRule ^'
            . $rootName
            . '(.*)$ '
            . 'index.php?q=$1'
            . ' [L,QSA]'
            . PHP_EOL;
        // Remove any duplicate multilingual settings...
        $serverSettings = array_unique( $serverSettings );

        // Order them by the size of the root name
        asort( $serverSettingsOrder );
        foreach ( $serverSettingsOrder as $counter => $order )
        {
          $output .= $serverSettings[$counter];
        }
      }
      else
      {
//        $output .=
//          '# Friendly URLs' . PHP_EOL
//          . 'RewriteEngine On' . PHP_EOL
//          . 'RewriteBase /' . $this->GetMODxSubdirectory( false, false ) . PHP_EOL;
        $output .=
          '# The Friendly URLs part' . PHP_EOL;
        $serverName = $this->GetActiveServerName( NULL );
        $output .=
          'RewriteCond %{HTTP_HOST} ^'
            . str_replace( '.', '\.', $serverName )
            . '$'
            . PHP_EOL;
        $output .=
          'RewriteCond %{REQUEST_FILENAME} !-f' . PHP_EOL
          . 'RewriteCond %{REQUEST_FILENAME} !-d' . PHP_EOL;
        $rootName = $this->GetActiveRootName( NULL );
        if ( $rootName != '' )
        {
          $rootName = $rootName . '/';
        }
        $output .=
          'RewriteRule ^'
            . $rootName
            . '(.*)$ '
            . 'index.php?q=$1'
            . ' [L,QSA]'
            . PHP_EOL;

      }
      return $output . PHP_EOL;
    }

    public function GetUseLanguageDependentServerNames()
    {
      return $this->itsUseLanguageDependentServerNames;
    }

    public function GetUseLanguageDependentRootNames()
    {
      return $this->itsUseLanguageDependentRootNames;
    }

    public function GetUseLanguageQueryParam()
    {
      return $this->itsUseLanguageQueryParam;
    }

    public function GetUseUniqueMultilingualAliases()
    {
      return $this->itsUseUniqueMultilingualAliases;
    }

    public function CanUseLanguageDependentServerNames()
    {
      // The mono servername and all the multilingual server names
      // must be set to be in language dependent server name mode
      if ( $this->itsMonoServerName == '' )
      {
        return FALSE;
      }
      foreach ( $this->itsActiveLangIds as $langId )
      {
        if ( ! array_key_exists( $langId, $this->itsMultiServerName ) )
        {
          return FALSE;
        }
        if ( $this->itsMultiServerName[ $langId ] == '' )
        {
          return FALSE;
        }
      }
      return TRUE;
    }

    public function SetCurrentLangId( $langId = NULL, $docId = NULL )
    {

      if ( is_null( $langId ) )
      {
        $this->itsCurrentLangId = $this->itsDefaultLangId;
        return TRUE;
      }

      // Cookie duration is currently hard wired...
      // but it would be nice to make it configurable
      if ( $this->IsActiveLangId( $langId ) )
      {
        $this->itsCurrentLangId = $langId;
        if ( $this->IsMultilingualDocument( $docId )  )
        {
          $success = setcookie(
            'yams_lang'
            , $this->itsCurrentLangId
            , time() + 604800
            , '/'
            );
        }
        return TRUE;
      }

      return FALSE;
    }

    public function DetermineCurrentLangId()
    {

      // Determines the current lang id
      // Also initialises the select and parse lang id
      if ( $this->itsMODx->insideManager() )
      {
        return FALSE;
      }

      if ( ! $this->itsUseLanguageQueryParam )
      {
        // First check to see if the lang has been set as a query parameter
        if ( isset( $_GET[ $this->UrlEncode( $this->itsLangQueryParam, FALSE )] ) )
        {
          $langId = $this->UrlDecode( $_GET[ $this->UrlEncode($this->itsLangQueryParam, FALSE) ] );
          if ( $this->IsActiveLangId( $langId ) )
          {
            return $langId;
          }
        }
      }

      // If not, then try to determine the lang
      // by checking what the headers are
      // // and by checking whether its supposed to be a multilingual document

      if ( $this->IsValidMultilingualRequest( $langId ) )
      {
        // This updates the cookie...
        if ( $this->IsActiveLangId( $langId ) )
        {
          return $langId;
        }
      }

      // So, either it is a monolingual document
      // or an unrecognised URL
      // If a language has previously been set,
      // then retrieve it from the cookie
      if ( isset( $_COOKIE['yams_lang'] ) )
      {
        $langId = $_COOKIE['yams_lang'];
        if ( $this->IsActiveLangId( $langId ) )
        {
          return $langId;
        }
      }

      // No language could be determined from the url or cookie.
      // How about the browser...
      switch ( $this->itsRedirectionMode )
      {
        case 'browser':
        case 'current_else_browser':
          return $this->GetBrowserLangId();
          break;
        default:
          return $this->itsDefaultLangId;
      }

    }

    public function SetFromCache( $fromCache )
    {
      $this->itsFromCache = $fromCache;
    }

    public function InitialiseParser(
      $isMultilingualDocument
      , $fromCache = NULL
      )
    {
      if ( $isMultilingualDocument
      || (
        $this->itsEasyLingualCompatibility
        // && $this->itsUseLanguageQueryParam
        )
      )
      {
        $this->itsSelectLangId = $this->itsCurrentLangId;
        $this->itsParseLangId = $this->itsCurrentLangId;
      }
      else
      {
        $this->itsSelectLangId = $this->itsDefaultLangId;
        $this->itsParseLangId = $this->itsDefaultLangId;
      }
      return TRUE;
    }

    public function IsValidId( $id )
    {
      return ctype_digit( (string) $id );
//      return (
//        preg_match(
//          '/^[0-9]+$/D'
//          . $this->itsEncodingModifier
//          , strval( $id )
//          )
//        == 1
//        );
    }

    public function IsValidLangGroupId( $langId )
    {
      return (
        preg_match(
          '/^[a-zA-Z0-9]+$/D'
          . $this->itsEncodingModifier
          , $langId
          )
        == 1
        );
    }

    public function IsValidRedirectionMode( $redirectionMode )
    {
      if ( !is_string( $redirectionMode ) )
      {
        return FALSE;
      }
      switch ( $redirectionMode )
      {
      case 'none':
      case 'default':
      case 'current':
      case 'current_else_browser':
      case 'browser':
        return TRUE;
        break;
      default:
        return FALSE;
      }
    }

    public function IsValidURLConversionMode( $urlConversionMode )
    {
      if ( !is_string( $urlConversionMode ) )
      {
        return FALSE;
      }
      switch ( $urlConversionMode )
      {
      case 'none':
      case 'default':
      case 'resolve':
        return TRUE;
        break;
      default:
        return FALSE;
      }
    }

    public function SaveCurrentSettings()
    {

      $contents = '<?php' . PHP_EOL;

      //----------------------------
      // itsActiveLangIds
      //----------------------------
      $contents .=
        '  // The ids of the active languages' . PHP_EOL
        . '  $this->itsActiveLangIds = array(' . PHP_EOL;

      $firstLangId = TRUE;
      foreach ( $this->itsActiveLangIds as $langId )
      {
        $comma = ', ';
        if ( $firstLangId )
        {
          $comma = '';
          $firstLangId = FALSE;
        }
        $contents .=
          '    '
          . $comma
          . '\''
          . addcslashes( $langId, '\'' )
          . '\''
          . PHP_EOL;
      }
      $contents .= '  );' . PHP_EOL;

      //----------------------------
      // itsInactiveLangIds
      //----------------------------
      $contents .=
        '  // The ids of the inactive languages' . PHP_EOL
        . '  $this->itsInactiveLangIds = array(' . PHP_EOL;

      $firstLangId = TRUE;
      foreach ( $this->itsInactiveLangIds as $langId )
      {
        $comma = ', ';
        if ( $firstLangId )
        {
          $comma = '';
          $firstLangId = FALSE;
        }
        $contents .=
          '    '
          . $comma
          . '\''
          . addcslashes( $langId, '\'' )
          . '\''
          . PHP_EOL;
      }
      $contents .= '  );' . PHP_EOL;

      //----------------------------
      // itsIsLTR
      //----------------------------
      $contents .=
        '  // The language direction (ltr or rtl)' . PHP_EOL
        . '  $this->itsIsLTR = array(' . PHP_EOL;
      $firstIsLTR = TRUE;
      foreach ( $this->itsIsLTR as $langId => $isLTR )
      {
        $comma = ', ';
        if ( $firstIsLTR )
        {
          $comma = '';
          $firstIsLTR = FALSE;
        }
        if ( $isLTR )
        {
          $isLTRText = 'TRUE';
        }
        else
        {
          $isLTRText = 'FALSE';
        }
        $contents .=
          '    '
          . $comma
          . '\''
          . $langId
          . '\''
          . ' => '
          . $isLTRText
          . PHP_EOL;
      }
      $contents .= '  );' . PHP_EOL;

      //----------------------------
      // itsDefaultLangId
      //----------------------------
      $contents .=
        '  // The default language id' . PHP_EOL
        . '  $this->itsDefaultLangId = \''
        . addcslashes( $this->itsDefaultLangId, '\'' )
        . '\';' . PHP_EOL;

      //----------------------------
      // itsRootName
      //----------------------------
      $contents .=
        '  // The name of the root folder' . PHP_EOL
        . '  $this->itsRootName = array(' . PHP_EOL;
      $firstRootName = TRUE;
      foreach ( $this->itsRootName as $langId => $rootName )
      {
        $comma = ', ';
        if ( $firstRootName )
        {
          $comma = '';
          $firstRootName = FALSE;
        }
        $contents .=
          '    '
          . $comma
          . '\''
          . addcslashes( $langId, '\'' )
          . '\''
          . ' => '
          . '\''
          . addcslashes( $rootName, '\'' )
          . '\''
          . PHP_EOL;
      }
      $contents .= '  );' . PHP_EOL;

      //----------------------------
      // itsMonoServerName
      //----------------------------
      $contents .=
        '  // The monolingual page server name' . PHP_EOL
        . '  $this->itsMonoServerName = \''
        . addcslashes( $this->itsMonoServerName, '\'' )
        . '\';' . PHP_EOL;

      //----------------------------
      // itsMultiServerName
      //----------------------------
      $contents .=
        '  // The server name for each language' . PHP_EOL
        . '  $this->itsMultiServerName = array(' . PHP_EOL;
      $firstServerName = TRUE;
      foreach ( $this->itsMultiServerName as $langId => $serverName )
      {
        $comma = ', ';
        if ( $firstServerName )
        {
          $comma = '';
          $firstServerName = FALSE;
        }
        $contents .=
          '    '
          . $comma
          . '\''
          . addcslashes( $langId, '\'' )
          . '\''
          . ' => '
          . '\''
          . addcslashes( $serverName, '\'' )
          . '\''
          . PHP_EOL;
      }
      $contents .= '  );' . PHP_EOL;

      //----------------------------
      // itsLangNames
      //----------------------------
      $contents .=
        '  // The name of each language in all languages' . PHP_EOL
        . '  $this->itsLangNames = array(' . PHP_EOL;
      $firstLangId = TRUE;
      foreach ( $this->itsLangNames as $langId => $langArray )
      {
        $comma = ', ';
        if ( $firstLangId )
        {
          $comma = '';
          $firstLangId = FALSE;
        }
        if ( !is_array( $langArray ) )
        {
          continue;
        }
        $contents .=
          '    '
          . $comma
          . '\''
          . addcslashes( $langId, '\'' )
          . '\''
          . ' => array(' . PHP_EOL;
        $innerFirstLangId = TRUE;
        foreach ( $langArray as $innerLangId => $langName )
        {
          $innerComma = ', ';
          if ( $innerFirstLangId )
          {
            $innerComma = '';
            $innerFirstLangId = FALSE;
          }
          $contents .=
            '      '
            . $innerComma
            . '\''
            . addcslashes( $innerLangId, '\'' )
            . '\''
            . ' => \''
            . addcslashes( $langName, '\'' )
            . '\'' . PHP_EOL;
        }
        $contents .=
          '    )' . PHP_EOL;
      }
      $contents .= '  );' . PHP_EOL;

      //----------------------------
      // itsChooseLangText
      //----------------------------
      // 'Select language' or 'Choose language' text
      // in each language
      $contents .=
        '  $this->itsChooseLangText = array(' . PHP_EOL;
      $firstLang = TRUE;
      foreach ( $this->itsChooseLangText as $langId => $text )
      {
        $comma = ', ';
        if ( $firstLang )
        {
          $firstLang = FALSE;
          $comma = '';
        }
        $contents .=
          '    '
          . $comma
          . '\''
          . addcslashes( $langId, '\'' )
          . '\''
          . ' => '
          . '\''
          . addcslashes( $text, '\'' )
          . '\''
          . PHP_EOL;
      }
      $contents .=
        '    );' . PHP_EOL;

      //----------------------------
      // itsLangTags
      //----------------------------
      $contents .=
        '  // The languages that should be directed to this language root.' . PHP_EOL
        . '  // These should be in priority order' . PHP_EOL
        . '  // The tag is in the format provided by the HTTP Accept-Language header:' . PHP_EOL
        . '  // xx, or xx-yy, where' . PHP_EOL
        . '  // xx: is a two letter language abbreviation' . PHP_EOL
        . '  //     http://www.loc.gov/standards/iso639-2/php/code_list.php' . PHP_EOL
        . '  // yy: is a two letter country code' . PHP_EOL
        . '  //     http://www.iso.org/country_codes/iso_3166_code_lists/english_country_names_and_code_elements.htm' . PHP_EOL
        . '  // xx on its own matches an xx Accept-Language header' . PHP_EOL
        . '  // with any country code' . PHP_EOL
        . '  // At least one language tag must be specified.' . PHP_EOL
        . '  $this->itsLangTags = array('  . PHP_EOL;
      $firstLang = TRUE;
      foreach ( $this->itsLangTags as $langId => $tagArray )
      {
        $comma = ', ';
        if ( $firstLang )
        {
          $comma = '';
          $firstLang = FALSE;
        }
        $contents .=
          '    '
          . $comma
          . '\''
          . addcslashes( $langId, '\'' )
          . '\' => array(' . PHP_EOL;
        $firstTag = TRUE;
        foreach ( $tagArray as $tag )
        {
          $innerComma = ', ';
          if ( $firstTag )
          {
            $innerComma = '';
            $firstTag = FALSE;
          }
          $contents .=
            '      '
            . $innerComma
            . '\''
            . addcslashes( $tag, '\'' )
            . '\' '
            . PHP_EOL;
        }
        $contents .=
          '      )' . PHP_EOL;
      }
      $contents .=
        '    );' . PHP_EOL;

      //----------------------------
      // itsMODxLangName
      //----------------------------
      $contents .=
        '  // The MODx manager language name for each language group.' . PHP_EOL
        . '  $this->itsMODxLangName = array('  . PHP_EOL;
      $firstLang = TRUE;
      foreach ( $this->itsMODxLangName as $langId => $modxLangName )
      {
        $comma = ', ';
        if ( $firstLang )
        {
          $comma = '';
          $firstLang = FALSE;
        }
        $contents .=
          '    '
          . $comma
          . '\''
          . addcslashes( $langId, '\'' )
          . '\' => \''
          . addcslashes( $modxLangName, '\'' )
          . '\''
          . PHP_EOL;
      }
      $contents .=
        '    );' . PHP_EOL;

      //----------------------------
      // itsEncodingModifierMode
      //----------------------------
      $contents .=
        '  // The encoding modifier.' . PHP_EOL
        . '  // \'manager\' means use the manager setting' . PHP_EOL
        . '  // \'u\' if webpage content is in UTF-8' . PHP_EOL
        . '  // \'\' otherwise' . PHP_EOL
        . '  $this->itsEncodingModifierMode = \''
        . addcslashes( $this->itsEncodingModifierMode, '\'' )
        . '\';' . PHP_EOL;

      //----------------------------
      // itsActiveTemplates
      //----------------------------
      $contents .=
        '  // a comma separated list of active template ids' . PHP_EOL
        . '  // if the default activity is none' . PHP_EOL
        . '  $this->itsActiveTemplates = array(' . PHP_EOL;
      $firstTemplate = TRUE;
      foreach ( $this->itsActiveTemplates as $templateId => $activeTVs )
      {
        $comma = ', ';
        if ( $firstTemplate )
        {
          $comma = '';
          $firstTemplate = FALSE;
        }
        $contents .=
          '    '
          . $comma
          . preg_replace(
            '[^0-9]'
            , ''
            , $templateId
            )
          . ' => ';
        if ( is_null( $activeTVs ) )
        {
          $contents .= 'NULL' . PHP_EOL;
        }
        elseif ( is_array( $activeTVs ) )
        {
          $contents .= 'array(' . PHP_EOL;
          $tvFirst = TRUE;
          foreach ( $activeTVs as $tv )
          {
            $comma = ', ';
            if ( $tvFirst )
            {
              $comma = '';
              $tvFirst = FALSE;
            }
            $contents .=
              '        '
              . $comma
              . '\''
              . addcslashes( $tv, '\'' )
              . '\'' . PHP_EOL;
          }
          $contents .= ')' . PHP_EOL;
        }
        else
        {
          $contents .= '        array()' . PHP_EOL;
        }
      }
      $contents .=
        '      );' . PHP_EOL;

      //----------------------------
      // itsManageTVs
      //----------------------------
      if ( $this->itsManageTVs )
      {
        $manageTVsText = 'TRUE';
      }
      else
      {
        $manageTVsText = 'FALSE';
      }
      $contents .=
        '  // Whether or not to manage template variables automatically' . PHP_EOL
        .  '  $this->itsManageTVs = '
        . $manageTVsText
        . ';' . PHP_EOL;

      //----------------------------
      // itsLangQueryParam
      //----------------------------
      $contents .=
        '  // The yams current lang query parameter name' . PHP_EOL
        . '  $this->itsLangQueryParam = \''
        . addcslashes( $this->itsLangQueryParam, '\'' )
        . '\';' . PHP_EOL;

      //----------------------------
      // itsChangeLangQueryParam
      //----------------------------
      $contents .=
        '  // The yams change lang query parameter name' . PHP_EOL
        . '  $this->itsChangeLangQueryParam = \''
        . addcslashes( $this->itsChangeLangQueryParam, '\'' )
        . '\';' . PHP_EOL;

      //----------------------------
      // itsRedirectionMode
      //----------------------------
      $contents .=
        '  // Turn on/off redirection from existing pages to multilingual pages' . PHP_EOL
        . '  // You can set to false if you are developing a site from scratch' . PHP_EOL
        . '  // - although leaving as TRUE does not harm in this instance' . PHP_EOL
        . '  // Set to TRUE if you are converting a website' . PHP_EOL
        . '  // that has already been made public' . PHP_EOL
        . '  $this->itsRedirectionMode = \''
        . addcslashes( $this->itsRedirectionMode, '\'' )
        . '\';' . PHP_EOL;

      //----------------------------
      // itsHTTPStatus
      //----------------------------
      $contents .=
        '  // The type of http redirection to perform when redirecting to the default language' . PHP_EOL
        . '  $this->itsHTTPStatus = '
        . preg_replace(
          '[^0-9]'
          , ''
          , $this->itsHTTPStatus
          )
        .';' . PHP_EOL;

      //----------------------------
      // itsHTTPStatusNotDefault
      //----------------------------
      $contents .=
        '  // The type of http redirection to perform when redirecting to a non-default language' . PHP_EOL
        . '  $this->itsHTTPStatusNotDefault = '
        . preg_replace(
          '[^0-9]'
          , ''
          , $this->itsHTTPStatusNotDefault
          )
        .';' . PHP_EOL;

      //----------------------------
      // itsHTTPStatusChangeLang
      //----------------------------
      $contents .=
        '  // The type of http redirection to perform when responding to a request to change language' . PHP_EOL
        . '  $this->itsHTTPStatusChangeLang = '
        . preg_replace(
          '[^0-9]'
          , ''
          , $this->itsHTTPStatusChangeLang
          )
        .';' . PHP_EOL;

      //----------------------------
      // itsHideFields
      //----------------------------
      if ( $this->itsHideFields )
      {
        $hideFieldsText = 'TRUE';
      }
      else
      {
        $hideFieldsText = 'FALSE';
      }
      $contents .=
        '  // Whether or not to hide the original fields' . PHP_EOL
        . '  $this->itsHideFields = '
        . $hideFieldsText
        . ';' . PHP_EOL;

      //----------------------------
      // itsTabifyLangs
      //----------------------------
      if ( $this->itsTabifyLangs )
      {
        $tabifyLangsText = 'TRUE';
      }
      else
      {
        $tabifyLangsText = 'FALSE';
      }
      $contents .=
        '  // Whether or not to place tvs for individual languages on separate tabs' . PHP_EOL
        . '  $this->itsTabifyLangs = '
        . $tabifyLangsText
        . ';' . PHP_EOL;

      //----------------------------
      // itsSynchronisePagetitle
      //----------------------------
      if ( $this->itsSynchronisePagetitle )
      {
        $synchronisePagetitleText = 'TRUE';
      }
      else
      {
        $synchronisePagetitleText = 'FALSE';
      }
      $contents .=
        '  // Whether or not to synchronise the document pagetitle with the default language pagetitle' . PHP_EOL
        . '  $this->itsSynchronisePagetitle = '
        . $synchronisePagetitleText
        . ';' . PHP_EOL;

      //----------------------------
      // itsEasyLingualCompatibility
      //----------------------------
      if ( $this->itsEasyLingualCompatibility )
      {
        $easyLingualCompatibilityText = 'TRUE';
      }
      else
      {
        $easyLingualCompatibilityText = 'FALSE';
      }
      $contents .=
        '  // Whether or not to to use EasyLingual Compatiblity Mode' . PHP_EOL
        . '  $this->itsEasyLingualCompatibility = '
        . $easyLingualCompatibilityText
        . ';' . PHP_EOL;

      //----------------------------
      // itsShowSiteStartAlias
      //----------------------------
      if ( $this->itsShowSiteStartAlias )
      {
        $showSiteStartAliasText = 'TRUE';
      }
      else
      {
        $showSiteStartAliasText = 'FALSE';
      }
      $contents .=
        '  // Whether or not to show the site_start document alias.' . PHP_EOL
        . '  $this->itsShowSiteStartAlias = '
        . $showSiteStartAliasText
        . ';' . PHP_EOL;

      //----------------------------
      // itsRewriteContainersAsFolders
      //----------------------------
      if ( $this->itsRewriteContainersAsFolders )
      {
        $rewriteContainersAsFoldersText = 'TRUE';
      }
      else
      {
        $rewriteContainersAsFoldersText = 'FALSE';
      }
      $contents .=
        '  // Whether or not to rewrite containers as folders.' . PHP_EOL
        . '  $this->itsRewriteContainersAsFolders = '
        . $rewriteContainersAsFoldersText
        . ';' . PHP_EOL;

      //----------------------------
      // itsMODxSubdirectory
      //----------------------------
      $contents .=
        '  // If MODx is installed into a subdirectory then this param' . PHP_EOL
        . '  // can be used to specify the path to that directory.' . PHP_EOL
        . '  // (with a trailing slash and no leading slash)' . PHP_EOL
        . '  $this->itsMODxSubdirectory = \''
        . addcslashes( $this->itsMODxSubdirectory, '\'' )
        . '\';' . PHP_EOL;

      //----------------------------
      // itsURLConversionMode
      //----------------------------
      $contents .=
        '  // The URL conversion mode' . PHP_EOL
        . '  // none: Don\'t do any automatic conversion of MODx URLs.' . PHP_EOL
        . '  // default: Convert MODx URLs surrounded by double quotes to (yams_doc:id) placeholders' . PHP_EOL
        . '  // resolve: Convert MODx URLs surrounded by double quotes to (yams_docr:id) placeholders' . PHP_EOL
        . '  $this->itsURLConversionMode = \''
        . addcslashes( $this->itsURLConversionMode, '\'' )
        . '\';' . PHP_EOL;

      //----------------------------
      // itsUseMultilingualAliases
      //----------------------------
      if ( $this->itsUseMultilingualAliases )
      {
        $useMultilingualAliasesText = 'TRUE';
      }
      else
      {
        $useMultilingualAliasesText = 'FALSE';
      }
      $contents .=
        '  // Whether or not to use multilingual aliases.' . PHP_EOL
        . '  $this->itsUseMultilingualAliases = '
        . $useMultilingualAliasesText
        . ';' . PHP_EOL;

      //----------------------------
      // itsMultilingualAliasesAreUnique
      //----------------------------
      if ( $this->itsMultilingualAliasesAreUnique )
      {
        $multilingualAliasesAreUniqueText = 'TRUE';
      }
      else
      {
        $multilingualAliasesAreUniqueText = 'FALSE';
      }
      $contents .=
        '  // Whether or not multilingual aliases are unique.' . PHP_EOL
        . '  $this->itsMultilingualAliasesAreUnique = '
        . $multilingualAliasesAreUniqueText
        . ';' . PHP_EOL;

      //----------------------------
      // itsUseMimeDependentSuffixes
      //----------------------------
      if ( $this->itsUseMimeDependentSuffixes )
      {
        $useMimeDependentSuffixesText = 'TRUE';
      }
      else
      {
        $useMimeDependentSuffixesText = 'FALSE';
      }
      $contents .=
        '  // Whether or not to use mime-dependent URL suffixes.' . PHP_EOL
        . '  $this->itsUseMimeDependentSuffixes = '
        . $useMimeDependentSuffixesText
        . ';' . PHP_EOL;

      //----------------------------
      // itsMimeSuffixMap
      //----------------------------
      $contents .=
        '  // The mime-type to suffix mapping.' . PHP_EOL
        . '  $this->itsMimeSuffixMap = array('  . PHP_EOL;
      $firstMimeType = TRUE;
      foreach ( $this->itsMimeSuffixMap as $mimeType => $suffix )
      {
        $comma = ', ';
        if ( $firstMimeType )
        {
          $comma = '';
          $firstMimeType = FALSE;
        }
        $contents .=
          '    '
          . $comma
          . '\''
          . addcslashes( $mimeType, '\'' )
          . '\' => \''
          . addcslashes( $suffix, '\'' )
          . '\''
          . PHP_EOL;
      }
      $contents .=
        '    );' . PHP_EOL;

      //----------------------------
      // itsLangRolesAccessMap
      //----------------------------
      $contents .=
        '  // A mapping from langIds to roles.' . PHP_EOL
        . '  // Says which roles have access to each language.' . PHP_EOL
        . '  // If an empty string is provided all roles have access' . PHP_EOL
        . '  // If no key is provided for a language all roles have access' . PHP_EOL
        . '  $this->itsLangRolesAccessMap = array('  . PHP_EOL;
      $firstLangId = TRUE;
      foreach ( $this->itsLangRolesAccessMap as $langId => $roles )
      {
        $comma = ', ';
        if ( $firstLangId )
        {
          $comma = '';
          $firstLangId = FALSE;
        }
        $contents .=
          '    '
          . $comma
          . '\''
          . addcslashes( $langId, '\'' )
          . '\' => \''
          . addcslashes( $roles, '\'' )
          . '\''
          . PHP_EOL;
      }
      $contents .=
        '    );' . PHP_EOL;
      
      //----------------------------
      // itsUseStripAlias
      //----------------------------
      if ( $this->itsUseStripAlias )
      {
        $useStripAliasText = 'TRUE';
      }
      else
      {
        $useStripAliasText = 'FALSE';
      }
      $contents .=
        '  // Whether or not to use stripAlias on multilingual aliases.' . PHP_EOL
        . '  $this->itsUseStripAlias = '
        . $useStripAliasText
        . ';' . PHP_EOL;

      //----------------------------
      // itsAcceptMODxURLDocIds
      //----------------------------
      $contents .=
        '  // An array of doc ids for which URLs of the form index.php?id= ... will be'
        . '  // accepted - even if friendly aliases are being used.' . PHP_EOL
        . '  // A * entry means all docIds.' . PHP_EOL
        . '  $this->itsAcceptMODxURLDocIds = array('  . PHP_EOL;
      $firstDocId = TRUE;
      foreach ( $this->itsAcceptMODxURLDocIds as $docId )
      {
        $comma = ', ';
        if ( $firstDocId )
        {
          $comma = '';
          $firstDocId = FALSE;
        }
        if ( ctype_digit( strval( $docId ) ) )
        {
          $contents .= '    ' . $comma . $docId . PHP_EOL;
        }
        else
        {
          $contents .= '    ' . $comma . '\'*\'' . PHP_EOL;
        }
      }
      $contents .=
        '    );' . PHP_EOL;
        
      //----------------------------

      $contents .=
        '?>';


      $file = fopen(
        dirname( __FILE__ ) . '/../yams.config.inc.php'
        , 'wb'
      );
      if ( $file === FALSE )
      {
        return FALSE;
      }
      $nBytes = fwrite(
        $file

        , $contents
      );

      if ( $nBytes === FALSE )
      {
        fclose( $file );
        return FALSE;
      }
      fflush( $file );
      fclose( $file );

      return TRUE;

    }

    public function GetSiteURL(
      $langId = NULL
      , $includeTrailingSlash = TRUE )
    {
      return $this->ConstructURL(
          $langId
          , NULL
          // , FALSE
          , TRUE
          , $includeTrailingSlash
          , FALSE
          , FALSE
          , FALSE );
    }

    public function ActiveURLsAreIdentical()
    {
      $serverNameAndRootArray = array();
      foreach ( $this->itsActiveLangIds as $langId )
      {
        $serverNameAndRootArray[ $langId ] =
          $this->GetServerNameAndRoot( $langId );
      }
      $nLangsAfterDuplicateRemoval =
        count( array_unique( $serverNameAndRootArray ) );
      if ( $nLangsAfterDuplicateRemoval == 1 )
      {
        return TRUE;
      }
      return FALSE;
    }

    public function ActiveURLsAreUnique()
    {
      $serverNameAndRootArray = array();
      foreach ( $this->itsActiveLangIds as $langId )
      {
        $serverNameAndRootArray[ $langId ] =
          $this->GetServerNameAndRoot( $langId );
      }
      $nActiveLangs =
        count( $this->itsActiveLangIds );
      $nLangsAfterDuplicateRemoval =
        count( array_unique( $serverNameAndRootArray ) );
      if ( $nActiveLangs != $nLangsAfterDuplicateRemoval )
      {
        return FALSE;
      }
      return TRUE;
    }

    public function GetIsLTR( $langId = NULL )
    {
      if ( is_null( $langId ) )
      {
        $langId = $this->itsDefaultLangId;
      }
      if (
        ! $this->IsActiveLangId( $langId )
        && ! $this->IsInactiveLangId( $langId )
        )
      {
        return TRUE;
      }
      if ( ! array_key_exists( $langId, $this->itsIsLTR ) )
      {
        return TRUE;
      }
      return $this->itsIsLTR[ $langId ];
    }

    public function GetLangDir( $langId = NULL )
    {
      $isLTR = $this->GetIsLTR( $langId );
      if ( $isLTR )
      {
        return 'ltr';
      }
      else
      {
        return 'rtl';
      }
    }

    public function GetLangAlign( $langId = NULL )
    {
      $isLTR = $this->GetIsLTR( $langId );
      if ( $isLTR )
      {
        return 'left';
      }
      else
      {
        return 'right';
      }
    }

    public function SetIsLTR(
      $langId
      , $isLTR
      , $save = TRUE )
    {
      if (
        ! $this->IsActiveLangId( $langId )
        && ! $this->IsInactiveLangId( $langId )
        )
      {
        return FALSE;
      }
      $exists = array_key_exists( $langId, $this->itsIsLTR );
      if (
        ( $exists && $this->itsIsLTR[ $langId ] != $isLTR ) || ! $exists )
      {
        if ( $isLTR )
        {
          $this->itsIsLTR[ $langId ] = TRUE;
        }
        else
        {
          $this->itsIsLTR[ $langId ] = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function SetRolesAccessList(
      $langId
      , $rolesAccessList
      , $save = TRUE )
    {
      if (
        ! $this->IsActiveLangId( $langId )
        && ! $this->IsInactiveLangId( $langId )
        )
      {
        return FALSE;
      }
      $exists = array_key_exists( $langId, $this->itsLangRolesAccessMap );
      if (
        ( $exists && $this->itsLangRolesAccessMap[ $langId ] != $rolesAccessList )
        || ! $exists
        )
      {
        if ( preg_match(
          '/^(|\!?[0-9]+(,\!?[0-9]+)*)$/'
            . $this->itsEncodingModifier
          , $rolesAccessList
          ) != 1
        )
        {
          return FALSE;
        }
        $this->itsLangRolesAccessMap[ $langId ] = $rolesAccessList;
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function GetRootName( $langId = NULL, $encoded = TRUE )
    {
      if ( is_null( $langId ) )
      {
        return '';
      }
      // Removed for improvement in efficiency.
//      if (
//        ! $this->IsActiveLangId( $langId )
//        && ! $this->IsInactiveLangId( $langId )
//        )
//      {
//        return '';
//      }
      if ( ! array_key_exists( $langId, $this->itsRootName ) )
      {
        return '';
      }
      if ( $encoded )
      {
        return $this->UrlEncode( $this->itsRootName[ $langId ] );
      }
      else
      {
        return $this->itsRootName[ $langId ];
      }
    }

    public function GetActiveRootName( $langId = NULL, $encoded = TRUE )
    {
      // like GetRootName, but returns an empty string
      // if query param mode is on
      if ( $this->itsUseLanguageQueryParam )
      {
        return '';
      }
      return $this->GetRootName( $langId, $encoded );
    }

    public function GetActiveLanguageDependentQueryParam( $langId = NULL )
    {
      if ( ! $this->itsUseLanguageQueryParam )
      {
        return '';
      }
      if ( is_null( $langId ) )
      {
        return '';
      }
      if ( ! $this->IsValidLangGroupId( $langId ) )
      {
        return '';
      }
      return
        $this->UrlEncode( $this->itsLangQueryParam )
         . '='
         . $this->UrlEncode( $langId );
    }

    public function SetRootName(
      $langId
      , $name
      , $save = TRUE )
    {
      if (
        ! $this->IsActiveLangId( $langId )
        && ! $this->IsInactiveLangId( $langId )
        )
      {
        return FALSE;
      }
      if ( ! is_string( $name ) || ( ! ctype_graph( $name ) && $name != '') )
      {
        return FALSE;
      }
      $this->itsRootName[ $langId ] = $name;
      $this->UpdateLanguageDependentRootNamesMode();
      $this->UpdateLanguageQueryParamMode();
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function GetServerName(
      $langId = NULL
      , $ignoreQueryParamMode = FALSE
      )
    {
      // The registered server name for a given language
      // If $langId is NULL, then returns the monolingual server name
      if ( is_null( $langId ) )
      {
        return $this->itsMonoServerName;
      }
      else
      {
        if (
          ! $this->IsActiveLangId( $langId )
          && ! $this->IsInactiveLangId( $langId )
          )
        {
          return '';
        }
        if ( ! array_key_exists( $langId, $this->itsMultiServerName ) )
        {
          return '';
        }
        return $this->itsMultiServerName[ $langId ];
      }
    }

    public function GetActiveServerName(
      $langId = NULL
      // , $stripPort = TRUE
      )
    {
      // The server name being used for a given language
      if ( $this->itsUseLanguageQueryParam )
      {
        return $this->GetHostName();
      }
      if ( ! $this->itsUseLanguageDependentServerNames )
      {
        return $this->GetHostName();
      }
      $serverName = $this->GetServerName( $langId );
      if ( $serverName == '' )
      {
        $serverName = $this->GetHostName();
      }

      return $serverName;
    }

    public function SetMonoServerName( $name, $save = TRUE )
    {
      if (
        ! is_string( $name )
        || preg_match(
          '/^' . YAMS_RE_SERVER_NAME . '$/i'
          . $this->itsEncodingModifier
          , $name
          ) != 1)
      {
        return FALSE;
      }
      $this->itsMonoServerName = $name;
      // Update the language dependent server name mode
      $this->UpdateLanguageDependentServerNamesMode();
      $this->UpdateLanguageQueryParamMode();
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function SetServerName(
      $langId
      , $name
      , $save = TRUE )
    {
      if (
        ! $this->IsActiveLangId( $langId )
        && ! $this->IsInactiveLangId( $langId )
        )
      {
        return FALSE;
      }
      if (
        ! is_string( $name )
        || preg_match(
          '/^' . YAMS_RE_SERVER_NAME . '$/i'
          . $this->itsEncodingModifier
          , $name
          ) != 1)
      {
        return FALSE;
      }
      $this->itsMultiServerName[ $langId ] = $name;
      // Update the language dependent server name mode
      $this->UpdateLanguageDependentServerNamesMode();
      $this->UpdateLanguageQueryParamMode();
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function GetLangName( $inLangId, $whichLangId = NULL )
    {
      if (
        ! $this->IsActiveLangId( $inLangId )
        && ! $this->IsInactiveLangId( $inLangId )
        )
      {
        return '';
      }
      if ( is_null( $whichLangId ) )
      {
        $whichLangId = $inLangId;
      }
      if (
        ! $this->IsActiveLangId( $whichLangId )
        && ! $this->IsInactiveLangId( $whichLangId )
        )
      {
        return '';
      }
      if ( ! array_key_exists( $inLangId, $this->itsLangNames ) )
      {
        return '';
      }
      $langNames = $this->itsLangNames[ $inLangId ];
      if ( ! array_key_exists( $whichLangId, $langNames ) )
      {
        return '';
      }
      return $langNames[ $whichLangId ];

    }

    public function IsActiveLangId( $langId )
    {
//      if ( ! is_string( $langId ) )
//      {
//        return FALSE;
//      }
      if ( in_array( $langId, $this->itsActiveLangIds ) )
      {
        return TRUE;
      }
      return FALSE;
    }

    public function IsInactiveLangId( $langId )
    {
      if ( ! is_string( $langId ) )
      {
        return FALSE;
      }
      if ( in_array( $langId, $this->itsInactiveLangIds ) )
      {
        return TRUE;
      }
      return FALSE;
    }

    public function MultiLangExpand(
      $get
      , $from
      , $docId = NULL
      , $mode = ''
      , $beforeModifier = ''
      , $afterModifier = ''
      )
    {

      $multiLangString = TRUE;
      $langNameArray = preg_split(
        '/\|\|/'
          . $this->itsEncodingModifier
        , $from
        , -1
        );
      foreach ( $langNameArray as $langName )
      {
        $result = preg_match(
          '/^[a-zA-Z0-9]+::/DU'
            . $this->itsEncodingModifier
          , $langName
          );
        if ( $result != 1 )
        {
          $multiLangString = FALSE;
          break;
        }
      }
//      if ( preg_match(
//        '/^[a-zA-Z0-9]+:(|.*[^\\\\])(\|[a-zA-Z0-9]+:(|.*[^\\\\]))*$/DU'
//        // '/^[a-zA-Z0-9]+:(.*)(\|\|[a-zA-Z0-9]+:(.*))*$/DU'
//        // '/^[a-zA-Z0-9]+\=\=.*(\|\|[a-zA-Z0-9]+\=\=.*)*$/DU'
//          . $this->itsEncodingModifier
//        , $from
//        ) == 1 )
      if ( $multiLangString )
      {
        $select = $from;
      }
      else
      {
        $select = array();
        if (
          ( $get == 'content' )
          && $this->IsValidId($docId)
          && ! $this->IsMultilingualDocument( $docId ) )
        {
          foreach ( $this->GetActiveLangIds() as $langId )
          {
            $select[ $langId ] = $from;
          }
        }
        else
        {
          // error_log( $from );
          foreach ( $this->GetActiveLangIds() as $langId )
          {
            $select[ $langId ] = $from . '_' . $langId;
          }
        }
      }

      return $this->Expand(
        $get
        , $select
        , $docId
        , $mode
        , $beforeModifier
        , $afterModifier
      );

    }

    public function GetChooseLangText( $langId = NULL )
    {
      if ( is_null( $langId ) )
      {
        $langId = $this->itsCurrentLangId;
      }
      if ( ! array_key_exists( $langId, $this->itsChooseLangText ) )
      {
        return '';
      }
      return $this->itsChooseLangText[ $langId ];
    }

    public function GetMODxLangName( $langId = NULL )
    {
      if ( is_null( $langId ) )
      {
        $langId = $this->itsCurrentLangId;
      }
      if ( ! array_key_exists( $langId, $this->itsMODxLangName ) )
      {
        return '';
      }
      return $this->itsMODxLangName[ $langId ];
    }

    public function GetAcceptMODxURLDocIdsString( )
    {
      return implode( ',', $this->itsAcceptMODxURLDocIds );
    }

    public function SetChooseLangText( $langId, $text, $save = TRUE )
    {
      if (
        ! $this->IsActiveLangId( $langId )
        && ! $this->IsInactiveLangId( $langId )
        )
      {
        return FALSE;
      }
      if ( !is_string( $text ) )
      {
        return FALSE;
      }
      $this->itsChooseLangText[ $langId ] = $text;
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;

    }

    public function SetMODxLangName( $langId, $name, $save = TRUE )
    {
      if (
        ! $this->IsActiveLangId( $langId )
        && ! $this->IsInactiveLangId( $langId )
        )
      {
        return FALSE;
      }
      if ( !is_string( $name ) )
      {
        return FALSE;
      }
      $this->itsMODxLangName[ $langId ] = $name;
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;

    }

    public function SetAcceptMODxURLDocIdsString( $acceptMODxURLDocIdsString, $save = TRUE )
    {
      if ( !is_string( $acceptMODxURLDocIdsString ) )
      {
        return FALSE;
      }
      $newAcceptMODxURLDocIds = preg_split(
        '/\s*,\s*/x'
          . $this->itsEncodingModifier
        , $acceptMODxURLDocIdsString
        , -1
        , PREG_SPLIT_NO_EMPTY
        );
      if ( $newAcceptMODxURLDocIds === FALSE )
      {
        return FALSE;
      }
      foreach ( $newAcceptMODxURLDocIds as $id => $docId )
      {
        if ( $docId == '*' )
        {
          continue;
        }
        if ( $this->IsValidId( $docId ) )
        {
          continue;
        }
        unset( $newAcceptMODxURLDocIds[ $id ] );
      }
      $this->itsAcceptMODxURLDocIds = array_unique( $newAcceptMODxURLDocIds );
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;

    }

    public function GetHideFields()
    {
      return $this->itsHideFields;
    }

    public function GetTabifyLangs()
    {
      return $this->itsTabifyLangs;
    }

    public function GetSynchronisePagetitle()
    {
      return $this->itsSynchronisePagetitle;
    }

    public function GetEasyLingualCompatibility()
    {
      return $this->itsEasyLingualCompatibility;
    }

    public function GetUseMimeDependentSuffixes()
    {
      return $this->itsUseMimeDependentSuffixes;
    }

    public function GetUseStripAlias()
    {
      return $this->itsUseStripAlias;
    }

    public function GetShowSiteStartAlias()
    {
      return $this->itsShowSiteStartAlias;
    }

    public function GetRewriteContainersAsFolders()
    {
      return $this->itsRewriteContainersAsFolders;
    }

    public function GetUseMultilingualAliases()
    {
      return $this->itsUseMultilingualAliases;
    }

    public function GetMultilingualAliasesAreUnique()
    {
      return $this->itsMultilingualAliasesAreUnique;
    }

    public function SetHideFields( $hideFields, $save = TRUE )
    {
      if ( $hideFields != $this->itsHideFields )
      {
        if ( $hideFields )
        {
          $this->itsHideFields = TRUE;
        }
        else
        {
          $this->itsHideFields = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function SetTabifyLangs( $tabifyLangs, $save = TRUE )
    {
      if ( $tabifyLangs != $this->itsTabifyLangs )
      {
        if ( $tabifyLangs )
        {
          $this->itsTabifyLangs = TRUE;
        }
        else
        {
          $this->itsTabifyLangs = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function SetSynchronisePagetitle( $synchronisePagetitle, $save = TRUE )
    {
      if ( $synchronisePagetitle != $this->itsSynchronisePagetitle )
      {
        if ( $synchronisePagetitle )
        {
          $this->itsSynchronisePagetitle = TRUE;
        }
        else
        {
          $this->itsSynchronisePagetitle = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function SetEasyLingualCompatibility( $easyLingualCompatibility, $save = TRUE )
    {
      if ( $easyLingualCompatibility != $this->itsEasyLingualCompatibility )
      {
        if ( $easyLingualCompatibility )
        {
          $this->itsEasyLingualCompatibility = TRUE;
        }
        else
        {
          $this->itsEasyLingualCompatibility = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function SetUseMimeDependentSuffixes( $useMimeDependentSuffixes, $save = TRUE )
    {
      if ( $useMimeDependentSuffixes != $this->itsUseMimeDependentSuffixes )
      {
        if ( $useMimeDependentSuffixes )
        {
          $this->itsUseMimeDependentSuffixes = TRUE;
        }
        else
        {
          $this->itsUseMimeDependentSuffixes = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function SetUseStripAlias( $useStripAlias, $save = TRUE )
    {
      if ( $useStripAlias != $this->itsUseStripAlias )
      {
        if ( $useStripAlias )
        {
          $this->itsUseStripAlias = TRUE;
        }
        else
        {
          $this->itsUseStripAlias = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function SetShowSiteStartAlias( $showSiteStartAlias, $save = TRUE )
    {
      if ( $showSiteStartAlias != $this->itsShowSiteStartAlias )
      {
        if ( $showSiteStartAlias )
        {
          $this->itsShowSiteStartAlias = TRUE;
        }
        else
        {
          $this->itsShowSiteStartAlias = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function SetRewriteContainersAsFolders( $rewriteContainersAsFolders, $save = TRUE )
    {
      if ( $rewriteContainersAsFolders != $this->itsRewriteContainersAsFolders )
      {
        if ( $rewriteContainersAsFolders )
        {
          $this->itsRewriteContainersAsFolders = TRUE;
        }
        else
        {
          $this->itsRewriteContainersAsFolders = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function SetUseMultilingualAliases(
      $useMultilingualAliases
      , $save = TRUE )
    {
      if ( $useMultilingualAliases != $this->itsUseMultilingualAliases )
      {
        if ( $useMultilingualAliases )
        {
          $this->itsUseMultilingualAliases = TRUE;
        }
        else
        {
          $this->itsUseMultilingualAliases = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function SetMultilingualAliasesAreUnique(
      $multilingualAliasesAreUnique
      , $save = TRUE )
    {
      if ( $multilingualAliasesAreUnique != $this->itsMultilingualAliasesAreUnique )
      {
        if ( $multilingualAliasesAreUnique )
        {
          $this->itsMultilingualAliasesAreUnique = TRUE;
        }
        else
        {
          $this->itsMultilingualAliasesAreUnique = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function IsMultilingualTemplate(
      $templateId
    )
    {
      if ( array_key_exists( $templateId, $this->itsActiveTemplates ) )
      {
        return TRUE;
      }
      return FALSE;
    }
    
    public function IsMultilingualDocument(
      $docId = NULL
//      , $template = NULL
    )
    {
      if ( is_null( $docId ) )
      {
        $docId = $this->itsMODx->documentIdentifier;
      }
      if ( ! $this->IsValidId( $docId ) )
      {
        return FALSE;
      }
      if ( in_array( $docId, $this->itsMonolingualDocIds ) )
      {
        return FALSE;
      }
      return TRUE;
//      if ( is_null( $template ) )
//      {
//        // Get the template of the specified document
//        $result = $this->itsMODx->getPageInfo( $docId, 0, 'template');
//        if ( ! is_array( $result ) )
//        {
//          return FALSE;
//        }
//        $template = $result['template'];
//      }
//      if ( array_key_exists(
//            $template
//            , $this->itsActiveTemplates
//            ) )
//      {
//        return TRUE;
//      }
//      return FALSE;

    }

    public function GetTemplateInfo( &$info )
    {
      global $_lang;

      $tablePre = $this->itsMODx->db->config['dbase'] . '.`' . $this->itsMODx->db->config['table_prefix'];
      $resourceTable = 'site_templates';
      $pluginsql = ( $resourceTable == 'site_plugins' ) ? $tablePre.$resourceTable.'`.disabled, ' : '';
      $orderby = ( $resourceTable == 'site_plugins' ) ? '6,2' : '5,1';
      $nameField = 'templatename';

      $sql = 'SELECT '
        . $pluginsql
        . $tablePre
        . $resourceTable
        . '`.'
        . $nameField
        . ' as name, '
        . $tablePre
        . $resourceTable
        . '`.id, '
        . $tablePre
        . $resourceTable
        . '`.description, '
        . $tablePre
        . $resourceTable
        . '`.locked, if(isnull('
        . $tablePre
        . 'categories`.category),\''
        . $_lang['no_category']
        . '\','
        . $tablePre
        . 'categories`.category) as category FROM '
        . $tablePre
        . $resourceTable
        . '` left join '
        . $tablePre
        . 'categories` on '
        . $tablePre
        . $resourceTable
        . '`.category = '
        . $tablePre
        . 'categories`.id ORDER BY '
        . $orderby;

      $info = $this->itsMODx->db->query( $sql );
      return mysql_num_rows( $info );

    }

    public function GetManageTVs()
    {
      return $this->itsManageTVs;
    }

    public function SetManageTVs( $manageTVs, $save = TRUE )
    {
      if ( $manageTVs != $this->itsManageTVs )
      {
        if ( $manageTVs )
        {
          $this->itsManageTVs = TRUE;
        }
        else
        {
          $this->itsManageTVs = FALSE;
        }
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;

    }

    public function SetRedirectionMode( $redirectionMode, $save = TRUE )
    {
      if ( ! $this->IsValidRedirectionMode( $redirectionMode ) )
      {
        return FALSE;
      }
      $this->itsRedirectionMode = $redirectionMode;
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function GetRedirectionMode()
    {
      return $this->itsRedirectionMode;
    }

    public function SetURLConversionMode( $urlConversionMode, $save = TRUE )
    {
      if ( ! $this->IsValidURLConversionMode( $urlConversionMode ) )
      {
        return FALSE;
      }
      $this->itsURLConversionMode = $urlConversionMode;
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function GetURLConversionMode()
    {
      return $this->itsURLConversionMode;
    }

    public function GetHTTPStatus()
    {
      return $this->itsHTTPStatus;
    }

    public function GetHTTPStatusNotDefault()
    {
      return $this->itsHTTPStatusNotDefault;
    }

    public function GetHTTPStatusChangeLang()
    {
      return $this->itsHTTPStatusChangeLang;
    }

    public function SetHTTPStatus( $status, $save = TRUE )
    {
      switch ( $status )
      {
      case 300: // multiple choices
      case 301: // permanent
      case 302: // found
      case 303: // see other
      case 307: // temporary redirect
        break;
      default:
        return FALSE;
      }
      $this->itsHTTPStatus = $status;
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function SetHTTPStatusNotDefault( $status, $save = TRUE )
    {
      switch ( $status )
      {
      case 300: // multiple choices
      case 301: // permanent
      case 302: // found
      case 303: // see other
      case 307: // temporary redirect
        break;
      default:
        return FALSE;
      }
      $this->itsHTTPStatusNotDefault = $status;
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function SetHTTPStatusChangeLang( $status, $save = TRUE )
    {
      switch ( $status )
      {
      case 300: // multiple choices
      case 301: // permanent
      case 302: // found
      case 303: // see other
      case 307: // temporary redirect
        break;
      default:
        return FALSE;
      }
      $this->itsHTTPStatusChangeLang = $status;
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function GetActiveTemplates()
    {
      return array_keys( $this->itsActiveTemplates );
    }

    public function GetActiveTemplatesList()
    {
      return implode( ',', array_keys( $this->itsActiveTemplates ) );
    }

    public function SetActiveTemplates( $activeTemplates, $save = TRUE )
    {
      if ( !is_array( $activeTemplates ) )
      {
        return FALSE;
      }
      foreach ( $activeTemplates as $templateId => $activeTVs )
      {
        if ( ! $this->IsValidId( $templateId ) )
        {
          return FALSE;
        }
        if ( is_null( $activeTVs ) )
        {
          break;
        }
        if ( !is_array( $activeTVs ) )
        {
          return FALSE;
        }
        foreach ( $activeTVs as $tv )
        {
          if ( ! is_string( $tv ) )
          {
            return FALSE;
          }
          if ( ! in_array( $tv, $this->itsDocVarNames ) )
          {
            return FALSE;
          }
//          switch ( $tv )
//          {
//          case 'pagetitle':
//          case 'longtitle':
//          case 'description':
//          case 'introtext':
//          case 'menutitle':
//          case 'content':
//            break;
//          default:
//            return FALSE;
//          }
        }
      }
      $this->itsActiveTemplates = $activeTemplates;
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function GetCurrentLangId()
    {
      return $this->itsCurrentLangId;
    }

    public function GetDefaultLangId()
    {
      return $this->itsDefaultLangId;
    }

    public function SetDefaultLangId( $langId, $save = TRUE )
    {
      if ( $langId != $this->itsDefaultLangId )
      {
        if ( ! $this->IsActiveLangId( $langId ) )
        {
          return FALSE;
        }
        $this->itsDefaultLangId = $langId;
        if ( $save )
        {
          return $this->SaveCurrentSettings();
        }
      }
      return TRUE;
    }

    public function GetLangNames()
    {
      return $this->itsLangNames;
    }

    public function GetActiveLangIds()
    {
      return $this->itsActiveLangIds;
    }

    public function GetInactiveLangIds()
    {
      return $this->itsInactiveLangIds;
    }

    public function GetPrimaryLangTag( $langId = NULL )
    {
      if ( is_null( $langId ) )
      {
        $langId = $this->itsCurrentLangId;
      }
      if ( ! array_key_exists( $langId, $this->itsLangTags ) )
      {
        return '';
      }
      return $this->itsLangTags[ $langId ][ 0 ];
    }

    public function GetLangTagsText( $langId = NULL )
    {
      if ( is_null( $langId ) )
      {
        $langId = $this->itsCurrentLangId;
      }
      if ( !array_key_exists( $langId, $this->itsLangTags ) )
      {
        return '';
      }
      return implode(',', $this->itsLangTags[ $langId ] );
    }

    public function ActivateLangId(
      $langId
      , $save = TRUE
      )
    {
      $success = $this->RemoveInactiveLangId( $langId );
      if ( !$success )
      {
        return FALSE;
      }
      $success = $this->AddActiveLangId( $langId );
      if ( !$success )
      {
        return FALSE;
      }
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;

    }

    public function DeactivateLangId(
      $langId
      , $save = TRUE
      )
    {
      $success = $this->RemoveActiveLangId( $langId );
      if ( !$success )
      {
        return FALSE;
      }
      $success = $this->AddInactiveLangId( $langId );
      if ( !$success )
      {
        return FALSE;
      }
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;

    }

    public function AddLang(
      $langId
      , $tags
      , $chooseLangText
      , $modxLangName
      , $rootName
      , $serverName
      , $langNames
      , $isLTR
      , $save = TRUE
    )
    {
      if ( !is_string( $langId ) || ! ctype_graph( $langId ) )
      {
        return FALSE;
      }
      if ( $this->IsActiveLangId( $langId ) )
      {
        return FALSE;
      }
      if ( $this->IsInactiveLangId( $langId ) )
      {
        return FALSE;
      }
      if ( !is_array( $langNames ) )
      {
        return FALSE;
      }

      $success = $this->AddActiveLangId( $langId );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->SetLangTagsText( $langId, $tags, FALSE );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->SetRootName( $langId, $rootName, FALSE );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->SetServerName( $langId, $serverName, FALSE );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->SetIsLTR( $langId, $isLTR, FALSE );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->SetChooseLangText( $langId, $chooseLangText, FALSE );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      foreach ( $langNames as $whichLangId => $name )
      {
        $success = $this->SetLangName( $langId, $name, $whichLangId, FALSE );
        if ( ! $success )
        {
          $this->Reload();
          return FALSE;
        }
      }

      $success = $this->SetMODxLangName( $langId, $modxLangName, FALSE );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function DeleteLang(
      $langId
      , $save = TRUE
    )
    {
      if ( ! $this->IsInactiveLangId( $langId ) )
      {
        return FALSE;
      }

      $success = $this->RemoveInactiveLangId( $langId );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->RemoveLangTagsText( $langId );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->RemoveRootName( $langId );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->RemoveServerName( $langId );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->RemoveIsLTR( $langId );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->RemoveChooseLangText( $langId );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->RemoveMODxLangName( $langId );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      $success = $this->RemoveLangNames( $langId );
      if ( ! $success )
      {
        $this->Reload();
        return FALSE;
      }

      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function SetLangName(
      $inLangId
      , $name
      , $whichLangId = NULL
      , $save = TRUE
    )
    {
      if (
        ! $this->IsActiveLangId( $inLangId )
        && ! $this->IsInactiveLangId( $inLangId )
        )
      {
        return FALSE;
      }
      if ( is_null( $whichLangId ) )
      {
        $whichLangId = $inLangId;
      }
      if (
        ! $this->IsActiveLangId( $whichLangId )
        && ! $this->IsInactiveLangId( $whichLangId )
        )
      {
        return FALSE;
      }
      if ( !is_string( $name ) )
      {
        return FALSE;
      }
      if ( ! array_key_exists( $inLangId, $this->itsLangNames ) )
      {
        $this->itsLangNames[ $inLangId ] = array();
      }
      $this->itsLangNames[ $inLangId ][ $whichLangId ] = $name;
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function SetLangTagsText(
      $langId
      , $langTags
      , $save = TRUE
    )
    {
      if (
        ! $this->IsActiveLangId( $langId )
        && ! $this->IsInactiveLangId( $langId )
        )
      {
        return FALSE;
      }
      if ( !is_string( $langTags ) )
      {
        return FALSE;
      }
      preg_match_all(
        '/([a-z]{1,8}(-[a-z]{1,8})?)/i'
        , $langTags
        , $parsedLangTags
      );
      if ( count( $parsedLangTags[1] ) < 1 )
      {
        return FALSE;
      }
      $this->itsLangTags[ $langId ] = $parsedLangTags[1];
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function Snippet(
      $get = ''
      , $from = ''
      , $docId = NULL
      , $beforetpl = NULL
      , $repeattpl = NULL
      , $currenttpl = NULL
      , $aftertpl = NULL
    )
    {
      // Determine which brackets to use depending on the action...
      switch( $get )
      {
      case 'list':
        // return $this->GetLanguageList();
        if ( !is_string( $beforetpl ) )
        {
          $beforetpl =
            '@FILE:assets/modules/yams/tpl/yams/list/before.tpl';
        }
        if ( !is_string( $repeattpl ) )
        {
          $repeattpl =
            '@FILE:assets/modules/yams/tpl/yams/list/repeat.tpl';
        }
        if ( !is_string( $currenttpl ) )
        {
          $currenttpl =
            '@FILE:assets/modules/yams/tpl/yams/list/current.tpl';
        }
        if ( !is_string( $aftertpl ) )
        {
          $aftertpl =
            '@FILE:assets/modules/yams/tpl/yams/list/after.tpl';
        }
        return $this->ExpandRepeatTemplates(
          $beforetpl
          , $repeattpl
          , $currenttpl
          , $aftertpl
          );
        break;
      case 'select':
        // return $this->GetLanguageSelect();
        if ( !is_string( $beforetpl ) )
        {
          $beforetpl =
            '@FILE:assets/modules/yams/tpl/yams/select/before.tpl';
        }
        if ( !is_string( $repeattpl ) )
        {
          $repeattpl =
            '@FILE:assets/modules/yams/tpl/yams/select/repeat.tpl';
        }
        if ( !is_string( $currenttpl ) )
        {
          $currenttpl =
            '@FILE:assets/modules/yams/tpl/yams/select/current.tpl';
        }
        if ( !is_string( $aftertpl ) )
        {
          $aftertpl =
            '@FILE:assets/modules/yams/tpl/yams/select/after.tpl';
        }
        return $this->ExpandRepeatTemplates(
          $beforetpl
          , $repeattpl
          , $currenttpl
          , $aftertpl
          );
        break;
      case 'selectform':
        // return $this->GetLanguageSelectForm();
        if ( !is_string( $beforetpl ) )
        {
          $beforetpl =
            '@FILE:assets/modules/yams/tpl/yams/selectform/before.tpl';
        }
        if ( !is_string( $repeattpl ) )
        {
          $repeattpl =
            '@FILE:assets/modules/yams/tpl/yams/selectform/repeat.tpl';
        }
        if ( !is_string( $currenttpl ) )
        {
          $currenttpl =
            '@FILE:assets/modules/yams/tpl/yams/selectform/current.tpl';
        }
        if ( !is_string( $aftertpl ) )
        {
          $aftertpl =
            '@FILE:assets/modules/yams/tpl/yams/selectform/after.tpl';
        }
        return $this->ExpandRepeatTemplates(
          $beforetpl
          , $repeattpl
          , $currenttpl
          , $aftertpl
          );
        break;
      case 'repeat':
        return $this->ExpandRepeatTemplates(
          $beforetpl
          , $repeattpl
          , $currenttpl
          , $aftertpl
          );
        break;
      case 'text':
      case 'chunk';
      case 'csnippet';
      case 'usnippet';
      case 'tv':
      case 'placeholder':
      case 'content':
      case 'data':
        return $this->MultiLangExpand(
          $get
          , $from
          , $docId
          , ''
          );
        break;
      default:
        return '';
      }

    }

    public function PreParse(
      &$content
      , $docId = NULL
      , $template = NULL
      , $isMultilingualDocument = NULL
      )
    {
      // Parses the web page
      // Replaces all document variables
      // by yams markup multilingual tvs

      // To avoid too many levels of recursion, this function
      // returns false if it needs to be called again, or
      // true otherswise

      // If the document hasn't changed... then don't
      // bother processing it...
      if ( is_string( $this->itsLastContentHash ) )
      {
        if ( md5( $content ) == $this->itsLastContentHash )
        {
          return TRUE;
        }
      }

//      if ( ! is_string( $content ) )
//      {
//        return TRUE;
//      }
//
      if ( is_null( $isMultilingualDocument ) )
      {
        $isMultilingualDocument = $this->IsMultilingualDocument(
            $docId
//            , $template
          );
      }

      if ( ! $this->IsValidId( $docId ) )
      {
        $docId = $this->itsMODx->documentIdentifier;
      }

      // Parse any chunks...
//      // Revert to the custom function based on mergeChunkContent.
//      // mergeChunkContent will delete unrecognised chunks...
//      // but a chunk might not be recognised at this stage because it
//      // contains a YAMS placeholder. These are only resolved
//      // after the PreParseOptimisation step.
      $changed = $this->MergeChunkContent( $content );
      if ( $changed )
      {
        // Try again
        return FALSE;
      }

//      // Parse YAMS markup
//      $success = $this->PreParseExpand(
//        $content
//        , $docId
//        , $template
//        , $isMultilingualDocument
//        );
//      if ( ! $success )
//      {
//        return FALSE;
//      }
      if ( $isMultilingualDocument )
      {
        // Expand out document variables ( [* and [+ form )
        // into their multilanguage equivalents...

        // A language section is started by
        // (yams-select:id)
        // // This is followed by
        // // (lang:id:default_lang_id) default language text
        // // where id is some integer identifier
        // // and default_lang_id is the default language (en)
        // It is followed by
        // (lang:id:other_lang_id) other language text
        // for as many other translations that are available,
        // where id is the same integer idenfifier
        // and other_lang_id is the language identifier for that language.
        // A language section is ended by
        // (/yams-select:id)
        $docVarNames = $this->GetDocVarNames();
        // preg_quote the names to be on the safe side...
        foreach ( $docVarNames as $key => $value )
        {
          $docVarNames[$key] = preg_quote( $value, '/' );
        }
        $multilingualPlaceHolderList = implode( '|', $docVarNames );
        $content
          = preg_replace_callback(
            '/\[(\+|\*)(#?)(' . $multilingualPlaceHolderList . ')((:.*)?)\1\]/U'
            . $this->itsEncodingModifier
            , array( $this, 'MultiLangCallback' )
            , $content
            , -1
          );
      }

      // Now the multilingual tvs have been sorted,
      // sort out the standard MODx tvs...
      $changed = $this->MergeDocumentContent( $content );
      if ( $changed )
      {
        // Try again
        return FALSE;
      }

      // Now merge the content from other documents...
      $changed = $this->MergeOtherDocumentContent( $content );
      if ( $changed )
      {
        // Try again
        return FALSE;
      }
      

      // Do automatic conversion of quoted URLs
      $outputURLFormat = NULL;
      switch( $this->itsURLConversionMode )
      {
        case 'default':
          $outputURLFormat = '"(yams_doc:\2)"';
          break;
        case 'resolve':
          $outputURLFormat = '"(yams_docr:\2)"';
          break;
      }
      if ( ! is_null( $outputURLFormat ) )
      {
        $content
          = preg_replace(
            '/"('
              . '\[\(site_url\)\]'
              . '|\[\(base_url\)\]'
              . '|' . preg_quote( $this->itsMODx->config['site_url'], '/' )
              . '|' . preg_quote( $this->itsMODx->config['base_url'], '/' )
              . '|'
              . ')'
              . '\[~(.*)~\]"/U'
              . $this->itsEncodingModifier
            , $outputURLFormat
            , $content
            , -1
          );
      }

      $content = $this->itsMODx->mergeSettingsContent( $content );

      $yamsPlaceHolderTypes = '(id|tag|root|root\/|\/root|site|server|doc|docr|dir|align|mname|confirm|change|name|(name_in_)([a-zA-Z0-9]+)|choose)';
      $easyLingualPlaceHolderTypes = '(lang|language|LANG|LANGUAGE|dir|align)';
      $this->itsCallbackDocId = $docId;
      $this->itsCallbackIsMultilingualDocument = $isMultilingualDocument;

      // Now expand out the yams variables
      // into their multilanguage equivalents
      $content
        = preg_replace_callback(
          '/\(yams_' . $yamsPlaceHolderTypes . '(|\+)(:([0-9]+))?\)/U'
          . $this->itsEncodingModifier
          , array($this, 'MultiLangYamsCallbackMulti')
          , $content
          , -1
        );

      if ( $this->itsEasyLingualCompatibility )
      {
        // Now expand out the yams variables
        // into their multilanguage equivalents
        $callback = array($this, 'MultiLangEasyLingualCallbackMulti');
        $content
          = preg_replace_callback(
            '/\[\%' . $easyLingualPlaceHolderTypes . '(|\+)\%\]/U'
            . $this->itsEncodingModifier
            , $callback
            , $content
            , -1
          );
      }

      // Optimise
      // Preparse optimise untangles select constructs and replaces them by
      // a single select construct.
      // This is quite resource intensive, so only do it if necessary...

      // Find the first match...
      $success = $this->PreParseOptimise(
        $content
        , $docId
        , $template
        , $isMultilingualDocument
        );
      if ( ! $success )
      {
        return TRUE;
      }

      // The optimisation may have created new valid chunks that can
      // be resolved.
      // Resolve them...
      $changed = $this->MergeChunkContent( $content );
      if ( $changed )
      {
        // Try again
        return FALSE;
      }

      // The optimisation may have introduced new tvs that can be parsed...
      $changed = $this->MergeDocumentContent( $content );
      if ( $changed )
      {
        // Try again
        return FALSE;
      }

      $content = $this->itsMODx->mergeSettingsContent( $content );
      
      $hash = md5( $content );
      $content = $this->itsMODx->evalSnippets( $content );
      if ( md5( $content ) != $hash )
      {
        // Try again
        return FALSE;
      }
      
      // At this stage all chunks, tvs and YAMS placeholders
      // have been resolved...
      // Time to let MODx take over with the snippet calls...

      $this->itsLastContentHash = md5( $content );
      
      return TRUE;
    }

    public function PreParseOptimise(
      &$content
      , $docId = NULL
      , $template = NULL
      , $isMultilingualDocument = NULL
      )
    {
      // Resolves all yams-select blocks into a single yams-select block,
      // while taking into account the yams-in blocks.

      if ( ! is_string( $content ) )
      {
        return FALSE;
      }

      // Only need to do the preparse optimisation
      // if there are nested yams-selects
      if ( preg_match(
          '/^\(yams-select(|\+):[0-9]{1,10}\)/'
            . $this->itsEncodingModifier
          , $content
          , $match
        ) == 1 )
      {
        $offset = strlen( $match[0] );
        // See if there is a second match...
        if ( preg_match(
            '/\(yams-select(|\+):[0-9]{1,10}\)/'
              . $this->itsEncodingModifier
            , $content
            , $match
            , PREG_OFFSET_CAPTURE
            , $offset
          ) != 1 )
        {
          return TRUE;
        }
      }
      
      if ( is_null( $isMultilingualDocument ) )
      {
        $isMultilingualDocument = $this->IsMultilingualDocument(
            $docId
//            , $template
          );
      }

      if ( ! $this->IsValidId( $docId ) )
      {
        $docId = $this->itsMODx->documentIdentifier;
      }

      $this->itsCallbackIsMultilingualDocument = $isMultilingualDocument;
//      if ( $content == '' )
//      {
//        $content = '<!--6-->';
//      }

      // Extract all yams-repeat blocks, store them in an array
      // and replace them by (yams-repeat-out/) placeholders
      $content
        = preg_replace_callback(
          '/'
            . '(?>\(yams-repeat:([0-9]{1,10})(:([^\)]+))?\))'
            . '(.*?)'
//            . '(\((current):\1\)(.*))?'
            . '\(\/yams-repeat:\1\)'
            . '/s'
            . $this->itsEncodingModifier
          , array($this, 'StoreYamsRepeatCallback')
          , $content
          , -1
          , $count
        );
//      if ( $content == '' )
//      {
//        $content = '<!--4-->';
//      }
      
      // Extract all yams-in blocks, store them in an array
      // and replace them by (yams-out/) placeholders
      $content
        = preg_replace_callback(
          '/'
            . '(?>\(yams-in:([0-9]{1,10})(:([^\)]+))?\))'
            . '(.*?)'
            . '\(\/yams-in:\1\)'
            . '/s'
            . $this->itsEncodingModifier
          , array($this, 'StoreYamsInCallback')
          , $content
          , -1
          , $count
        );
//      if ( $content == '' )
//      {
//        $content = '<!--5-->';
//      }

      // Need to do the same for any cached yams-repeat content.
      foreach ( $this->itsYamsRepeatContent as $counter => $cachedInfo )
      {
        $this->itsYamsRepeatContent[ $counter ][ 'content' ]
          = preg_replace_callback(
            '/'
              . '(?>\(yams-in:([0-9]{1,10})(:([^\)]+))?\))'
              . '(.*?)'
              . '\(\/yams-in:\1\)'
              . '/s'
              . $this->itsEncodingModifier
            , array($this, 'StoreYamsInCallback')
            , $this->itsYamsRepeatContent[ $counter ][ 'content' ]
            , -1
            , $count
          );
          
        if ( is_string( $this->itsYamsRepeatContent[ $counter ][ 'currentLangContent' ] ) )
        {
          $this->itsYamsRepeatContent[ $counter ][ 'currentLangContent' ]
            = preg_replace_callback(
              '/'
                . '(?>\(yams-in:([0-9]{1,10})(:([^\)]+))?\))'
                . '(.*?)'
                . '\(\/yams-in:\1\)'
                . '/s'
                . $this->itsEncodingModifier
              , array($this, 'StoreYamsInCallback')
              , $this->itsYamsRepeatContent[ $counter ][ 'currentLangContent' ]
              , -1
              , $count
            );
        }
      }


      // Replace all select blocks by a single select block...
      $optimisedOutputArray = array();
      foreach ( $this->itsActiveLangIds as $langId )
      {
        if (
          ( ! $isMultilingualDocument || $this->itsFromCache )
          && $this->itsParseLangId != $langId
          )
        {
          continue;
        }
        $oldParseLangId = $this->itsParseLangId;
        $oldSelectLangId = $this->itsSelectLangId;
        
        $this->itsParseLangId = $langId;        
        $this->itsSelectLangId = $langId;

        $optimisedOutputArray[ $langId ] = $content;
        $success = $this->PostParse(
            $isMultilingualDocument
            , $optimisedOutputArray[ $langId ]
          );
        if ( ! $success )
        {
          unset( $optimisedOutputArray[ $langId ] );
          $this->itsParseLangId = $oldParseLangId;
          $this->itsSelectLangId = $oldSelectLangId;
          continue;
        }
//        if ( $optimisedOutputArray[ $langId ] == '' )
//        {
//          $optimisedOutputArray[ $langId ] = '<!--3-->';
//        }
        // Find all the (yams-repeat-out/) placeholders
        // Parse the content in the appropriate language
        // the replace the content...
        // But keep the original yams-in (out) blocks
        // since these may contain uncacheable snippet calls
        // that may be resolved later.
        $optimisedOutputArray[ $langId ]
          = preg_replace_callback(
            '/'
              . '\(yams-repeat-out:([0-9]{1,10})\/\)'
              . '/U'
              . $this->itsEncodingModifier
            , array($this, 'RestoreYamsRepeatCallback')
            , $optimisedOutputArray[ $langId ]
            , -1
            , $count
          );

//        if ( $optimisedOutputArray[ $langId ] == '' )
//        {
//          $optimisedOutputArray[ $langId ] = '<!--2-->';
//        }
        // Find all the (yams-out/) placeholders
        // Parse the content in the appropriate language
        // the replace the content...
        // But keep the original yams-in block
        // since this may contain uncacheable snippet calls
        // that may be resolved later.
        $optimisedOutputArray[ $langId ]
          = preg_replace_callback(
            '/'
              . '\(yams-out:([0-9]{1,10})\/\)'
              . '/U'
              . $this->itsEncodingModifier
            , array($this, 'RestoreYamsInCallback')
            , $optimisedOutputArray[ $langId ]
            , -1
            , $count
          );

        $this->itsSelectLangId = $oldSelectLangId;
        $this->itsParseLangId = $oldParseLangId;

//        if ( $optimisedOutputArray[ $langId ] == '' )
//        {
//          $optimisedOutputArray[ $langId ] = '<!--1-->';
//        }
      }

      // Should be safe to clear the YamsInContent and YamsRepeatContent
      // caches here.
      unset( $this->itsYamsInContent );
      $this->itsYamsInContent = array();
      unset( $this->itsYamsRepeatContent );
      $this->itsYamsRepeatContent  = array();

      // Output a single select block...
      $content = $this->Expand(
        'text'
        , $optimisedOutputArray
        , $docId
        , ''
        );

      return TRUE;

    }

    public function PostParse(
      $isMultilingualDocument
      , &$content = NULL
      , $parseLangId = NULL
      , $preParse = TRUE
    )
    {
      // Recursively parses the web page
      // * Expands out multilanguage sections
      // * Selects the correct language text for single language sections

//      $nReplacements = 0;
      if ( !is_string( $content ) )
      {
//        $content = '<!--Parse:1-->';
        return FALSE;
      }

      $this->itsCallbackIsMultilingualDocument = $isMultilingualDocument;
      $oldParseLangId = $this->itsParseLangId;
      $oldSelectLangId = $this->itsSelectLangId;

//      if ( $content == '' )
//      {
//        $content = '<!--Parse:2-->';
//      }

      if (
          ! is_null( $parseLangId )
          && $this->IsActiveLangId( $parseLangId )
          )
      {
        $this->itsParseLangId = $parseLangId;
        $this->itsSelectLangId = $parseLangId;
      }

      // Parse the yams-in blocks in the correct language
      $content
        = preg_replace_callback(
          '/'
            . '(?>\(yams-in:([0-9]{1,10})(:([^\)]+))?\))'
            . '(.*?)'
            . '\(\/yams-in:\1\)'
            . '/s'
            . $this->itsEncodingModifier
          , array($this, 'YamsInCallback')
          , $content
          , -1
//          , $count
        );
//      $nReplacements += $count;

//      if ( $content == '' )
//      {
//        $content = '<!--Parse:3-->';
//      }

      // Select the correct language from multilanguage sections
      // Do the outer select block first...
        $content
          = preg_replace_callback(
            '/^(?>\(yams-select(|\+):([0-9]{1,10})\))'
            . '(.*)'
            . '\(\/yams-select\1:\2\)$/s'
            . $this->itsEncodingModifier
            , array($this, 'SelectLangCallback')
            , $content
            , 1
//            , $count
          );
//      $nReplacements += $count;

      // if ( $content == '' )
      // {
       // $content = '<!--Parse:' . $count . ':' . pcre_error_decode() . '-->';
      // }

      if ( $preParse )
      {

        do
        {
          $content
            = preg_replace_callback(
              '/(?>\(yams-select(|\+):([0-9]{1,10})\))'
              . '(.*?)'
              . '\(\/yams-select\1:\2\)/s'
              . $this->itsEncodingModifier
              , array($this, 'SelectLangCallback')
              , $content
              , -1
              , $count
            );
        } while ( $count > 0 );
//        $nReplacements += $count;
      }

//      if ( $content == '' )
//      {
//       $content = '<!--Parse 2:' . $count . ':' . pcre_error_decode() . '-->';
//      }


      $this->itsParseLangId = $oldParseLangId;
      $this->itsSelectLangId = $oldSelectLangId;

//      if ( $nReplacements > 0 )
//      {
//        $success = $this->PostParse(
//          $isMultilingualDocument
//          , $content
//          , $preParse
//        );
//        return $success;
//
//      }

      return TRUE;
    }

    private function PregQuoteReplacement( $str )
    {
      // See
      // http://www.procata.com/blog/archives/2005/11/13/two-preg_replace-escaping-gotchas/
      return preg_replace(
        '/(\$|\\\\)(?=\d)/' . $this->itsEncodingModifier
        , '\\\\\1'
        , $str);
    }

    private function WeblinkRedirect(
      $docId
      , $langId
    )
    {
      // Redirects to another page if the document is a weblink
      $docInfo = $this->itsMODx->getPageInfo(
        $docId
        , 0
        , 'type' );
      if ( !is_array( $docInfo ) )
      {
        return FALSE;
      }
      if ( $docInfo['type'] == 'reference' )
      {
        $resolvedURL = $this->ConstructResolvedURL(
          $langId
          , $docId
          // , TRUE
          , TRUE
          , TRUE
          , TRUE
          , TRUE
          , TRUE
          , TRUE
          , FALSE
          );
        if ( is_string( $resolvedURL ) && $resolvedURL != '' )
        {
          // error_log( 'url 1: ' . $url );
          header(
            'Location: ' . $resolvedURL
            , TRUE
            , 301
          );
          exit();
          return TRUE;
        }
      }

      return FALSE;

    }

    public function Redirect(
      $docId = NULL
      , $template = NULL
      , $docIdFoundByYAMS = NULL
      )
    {
      // error_log('Redirect');
      // Used to

      // a) switch to a new language page if requested
      // b) redirect from an invalid URL for a multilingual page to a valid URL
      // c) redirect from an invalid URL for a monolingual page to a valid URL

      // When redirecting to a multilingual page URL there are several language modes:
      // 'default'
      // - choose the default language
      // 'current'
      // - choose the current language
      // 'browser'
      // - the current browser language if available, or
      // - the default language

      // Returns TRUE if the page is redirected
      // and FALSE if it is not

      // If no valid page has been found ($docId == NULL)
      // then do the normal thing and let MODx go to page not found...
      if ( ! $this->IsValidId( $docId ) )
      {
        return FALSE;
      }

      // If there has been a request to change language,
      // via a get or post, but not a cookie, do so...
      if ( isset( $_GET[ $this->UrlEncode( $this->itsChangeLangQueryParam ) ] ) )
      {
        $newLangId = $this->UrlDecode( $_GET[ $this->UrlEncode( $this->itsChangeLangQueryParam ) ] );
      }
      elseif ( isset( $_POST[ $this->itsChangeLangQueryParam ] ) )
      {
        $newLangId = $_POST[ $this->itsChangeLangQueryParam ];
      }
      else
      {
        $newLangId = NULL;
      }
      if ( $this->itsUseLanguageQueryParam )
      {
        if ( isset( $_GET[ $this->UrlEncode( $this->itsLangQueryParam, FALSE ) ] ) )
        {
          $oldLangId = $this->UrlDecode( $_GET[ $this->UrlEncode( $this->itsLangQueryParam, FALSE ) ] );
        }
        else
        {
          $oldLangId = $this->itsCurrentLangId;
        }
      }
      else
      {
        $oldLangId = $this->itsCurrentLangId;
      }
      if ( is_string( $newLangId )
          && in_array( $newLangId, $this->itsActiveLangIds )
          && $newLangId != $oldLangId )
      {
        $url = $this->ConstructURL(
          $newLangId
          , $docId
          // , TRUE
          , TRUE
          , TRUE
          , TRUE
          , TRUE
          , TRUE
          , TRUE
          , FALSE
          );
        if ( $url != '' )
        {
          // error_log( 'url 1: ' . $url );
          header(
            'Location: ' . $url
            , TRUE
            , $this->itsHTTPStatusChangeLang
          );
          exit();
          return TRUE;
        }
      }

      if ( ! $docIdFoundByYAMS )
      {
        if (
          in_array( intval( $docId ), $this->itsAcceptMODxURLDocIds )
          || in_array( '*', $this->itsAcceptMODxURLDocIds )
          )
        {
        // Don't redirect unless it is a weblink
          return $this->WeblinkRedirect(
            $docId
            , $this->itsCurrentLangId
            );
        }
      }
//      $isIdPresent = isset( $_GET['id'] ) && ctype_digit( $_GET['id'] );
//      if ( $isIdPresent )
//      {
//        return $this->WeblinkRedirect(
//          $docId
//          , $this->itsCurrentLangId
//          );
//      }

      $isManagerPreviewPage = isset( $_GET['z'] ) && $this->UrlDecode( $_GET['z'] ) == 'manprev';
      if ( $isManagerPreviewPage )
      {
        // This is a manager preview page.
        // Don't redirect
        // header('HTTP/1.1 200 OK', TRUE, 200);
        return $this->WeblinkRedirect(
          $docId
          , $this->itsCurrentLangId
          );
      }

      // Determine the status code to use if a MODx standard URL was supplied,
      // for a mono/multilingual document URL and redirection is required

      $isMultilingualDocument
        = $this->IsMultilingualDocument(
          $docId
//          , $template
          );
      if ( $isMultilingualDocument )
      {
        if ( $this->IsValidMultilingualRequest( $langId ) )
        {
          if ( is_null( $langId ) )
          {
            // Now work out which language we should be directing to
            switch ( $this->itsRedirectionMode )
            {
            case 'current':
            case 'current_else_browser':
              $langId = $this->itsCurrentLangId;
              break;
            case 'browser':
              // Determine which language to redirect to
              // based on the request...
              $langId = $this->GetBrowserLangId();
              break;
            case 'default':
            default:
              $langId = $this->itsDefaultLangId;
            }
          }

          // This is the code to use when correcting errors in URLs
          $redirectStatus = 301;
          if ( ! $docIdFoundByYAMS )
          {
            // If it isn't a mistyped URL, but a MODx URL then...
            if ( $langId == $this->itsDefaultLangId )
            {
              $redirectStatus = $this->itsHTTPStatus;
            }
            else
            {
              $redirectStatus = $this->itsHTTPStatusNotDefault;
            }
          }
          
          $success = $this->RedirectToCanonicalURL(
            $docId
            , $langId
            , $redirectStatus );
          if ( $success )
          {
            return TRUE;
          }
          // Make sure the language is updated...
          $success = $this->SetCurrentLangId( $langId, $docId );
          // Don't need to do any redirecting
          // header('HTTP/1.1 200 OK', TRUE, 200);
          return $this->WeblinkRedirect(
            $docId
            , $this->itsCurrentLangId
            );
        }
        // // We need to redirect to the multilingual version of the page...
        // if ( $isManagePreviewPage )
        // {
          // // Don't redirect to a different document if within the manager
          // // preview pane
          // return FALSE:
        // }

        // First check the redirection mode
        if ( $this->itsRedirectionMode == 'none' )
        {
          // header('HTTP/1.1 200 OK', TRUE, 200);
          // Generate a page not found event...
          $this->itsMODx->documentIdentifier = NULL;
          return FALSE;
        }

        // Now work out which language we should be directing to
        switch ( $this->itsRedirectionMode )
        {
        case 'current':
        case 'current_else_browser':
          $chosenLangId = $this->itsCurrentLangId;
          break;
        case 'browser':
          // Determine which language to redirect to
          // based on the request...
          $chosenLangId = $this->GetBrowserLangId();
          break;
        case 'default':
        default:
          $chosenLangId = $this->itsDefaultLangId;
        }

        // Determine the HTTP status to use for redirection to chosen
        // language
        if ( $chosenLangId == $this->itsDefaultLangId )
        {
          $status = $this->itsHTTPStatus;
        }
        else
        {
          $status = $this->itsHTTPStatusNotDefault;
        }
      
        // Redirect to the chosen language
        $url = $this->ConstructURL(
            $chosenLangId
            , $docId
            // , TRUE
            , TRUE
            , TRUE
            , TRUE
            , TRUE
            , TRUE
            , TRUE
            , FALSE
          );
        if ( $url != '' )
        {
          // error_log( 'url 2: ' . $url );
          header(
            'Location: ' . $url
            , TRUE
            , $status
          );
          exit();
          return TRUE;
        }
      }
      else
      {

        // Not sure about the second branch of this OR...
        // Shouldn't this redirect to a valid monolingual URL
        // for the page? Can't remember why I wired it in now. I'm sure
        // it's important. Will need to test.
        if (
          $this->IsValidMonolingualRequest()
//          || (
//              $isValidMultilingualRequest
//              && $langId == $this->itsDefaultLangId
//             )
          )
        {
          // This is the code to use when correcting errors in URLs
          $redirectStatus = 301;
          if ( ! $docIdFoundByYAMS )
          {
            // If it isn't a mistyped URL, but a MODx URL then...
            $redirectStatus = $this->itsHTTPStatus;
          }
          $success = $this->RedirectToCanonicalURL(
            $docId
            , NULL
            , $redirectStatus);
          if ( $success )
          {
            return TRUE;
          }
          // header('HTTP/1.1 200 OK', TRUE, 200);
          return $this->WeblinkRedirect(
            $docId
            , NULL
            );
        }

        // Check for the scenario that the requested document is monolingual
        // but the request is for the multilingual version page
        $isValidMultilingualRequest = $this->IsValidMultilingualRequest( $langId );
        if ( $isValidMultilingualRequest )
        {
          // Redirect to the monolingual document in that language...
          $url = $this->ConstructURL(
              NULL
              , $docId
              // , TRUE
              , TRUE
              , TRUE
              , TRUE
              , TRUE
              , TRUE
              , TRUE
              , FALSE
            );
          if ( $url != '' )
          {
            // error_log( 'url 3: ' . $url );
            header(
              'Location: ' . $url
              , TRUE
              , $this->itsHTTPStatus
            );
            exit();
            return TRUE;
          }
        }

//        // If in EasyLingual compatibility mode,
//        // redirect to the requested language version.
//        // Otherwise, redirect to the correct, monolingual version of the page...
//        $status = $this->itsHTTPStatusChangeLang;
//        if ( ! $this->itsEasyLingualCompatibility )
//        {
//          $langId = NULL;
//          $status = $this->itsHTTPStatus;
//        }
//        $url = $this->ConstructURL(
//            $langId
//            , TRUE
//            , TRUE
//            , TRUE
//            , TRUE
//          );
//        if ( $url != '' )
//        {
//          // error_log( 'url 3: ' . $url );
//          header(
//            'Location: ' . $url
//            , TRUE
//            , $status
//          );
//          return TRUE;
//        }
      }

      // header('HTTP/1.1 404 Not Found', TRUE, 404);
      $this->itsMODx->documentIdentifier = NULL;
      return FALSE;
    }

    public function IsHTTPS()
    {
      global $https_port;
      if (
          (
            isset( $_SERVER['HTTPS'] )
            && $_SERVER['HTTPS'] != ''
            && strtolower( $_SERVER['HTTPS'] ) != 'off'
          )
          || $_SERVER['SERVER_PORT'] == $https_port
        )
      {
        return TRUE;
      }
      else
      {
        return FALSE;
      }
    }

    public function IsValidMonolingualRequest( )
    {
      // Need to check this in light of the introduction of
      // itsUseUniqueMultilingualAliases
      if ( $this->itsUseLanguageQueryParam )
      {
        return TRUE;
      }
      else
      {
        $hostName = $this->GetHostName();
        $serverName = $this->GetActiveServerName( NULL );
        if ( $hostName == $serverName )
        {
          return TRUE;
        }
        return FALSE;
      }
    }

    public function IsValidMultilingualRequest( &$outLangId = NULL )
    {
      // Set the outLangId if it is a valid multilingual request
      // or use NULL if not

      // In query param mode, this means that the query param is
      // specified and is a valid language group id.
      // Otherwise, the URL must be compatible with a multilingual
      // URL

      // If this has already been calculated, grab it from the cache.
      if ( ! is_null( $this->itsIsValidMultilingualDocument ) )
      {
        $outLangId = $this->itsRequestLangId;
        return $this->itsIsValidMultilingualDocument;
      }
      
//      if ( ! ( $this->itsRequestLangId === FALSE ) )
//      {
//        $outLangId = $this->itsRequestLangId;
//        if ( is_null( $outLangId ) )
//        {
//          return FALSE;
//        }
//        return TRUE;
//      }
//
      $outLangId = NULL;

      if ( $this->itsUseLanguageQueryParam )
      {
        if ( isset( $_GET[ $this->UrlEncode( $this->itsChangeLangQueryParam, FALSE ) ] ) )
        {
          $langId = $this->UrlDecode( $_GET[ $this->UrlEncode( $this->itsChangeLangQueryParam, FALSE ) ] );
          if ( in_array( $langId, $this->itsActiveLangIds ) )
          {
            $outLangId = $langId;
            $this->itsRequestLangId = $outLangId;
            $this->itsIsValidMultilingualDocument = TRUE;
            return $this->itsIsValidMultilingualDocument;
          }
          $this->itsRequestLangId = $outLangId;
          $this->itsIsValidMultilingualDocument = FALSE;
          return $this->itsIsValidMultilingualDocument;
        }
        if ( isset( $_GET[ $this->UrlEncode( $this->itsLangQueryParam, FALSE ) ] ) )
        {
          $langId = $this->UrlDecode( $_GET[ $this->UrlEncode( $this->itsLangQueryParam, FALSE ) ] );
          if ( in_array( $langId, $this->itsActiveLangIds ) )
          {
            $outLangId = $langId;
            $this->itsRequestLangId = $outLangId;
            $this->itsIsValidMultilingualDocument = TRUE;
            return $this->itsIsValidMultilingualDocument;
          }
          $this->itsRequestLangId = $outLangId;
          $this->itsIsValidMultilingualDocument = FALSE;
          return $this->itsIsValidMultilingualDocument;
        }
        $this->itsRequestLangId = $outLangId;
        $this->itsIsValidMultilingualDocument = FALSE;
        return $this->itsIsValidMultilingualDocument;
      }
      elseif ( $this->itsUseUniqueMultilingualAliases )
      {
        // The server and root name might not determine
        // the language uniquely...
        // Get the host name of the request...
        $hostName = $this->GetHostName();
        // Get the request URI, without any query stuff
        $requestURI = $_SERVER['REQUEST_URI'];
        $splitRequestURI = preg_split(
          '/\?/'
            . $this->itsEncodingModifier
          , $requestURI
          );
        $noQueryRequestURI = $splitRequestURI[0];

        $aliasEscaped = '(index\.php|)';
        if ( isset( $_GET['q'] ) )
        {
          // Get the alias path...
          $aliasDecoded = $_GET['q'];
          // split the path into subdirectories...
          $aliasArray = preg_split(
            '/' . preg_quote( '/', '/' )  . '/'
              . $this->itsEncodingModifier
            , $aliasDecoded
            );
          // Encode each subdirectory part
          foreach ( $aliasArray as $key => $value )
          {
            $aliasArray[ $key ] = $this->UrlEncode( $value );
          }
          // Reform the encoded url and preg quote it.
          $aliasEscaped = preg_quote( implode( '/', $aliasArray ), '/' );
        }

        $modxSubdirectoryEscaped =
          preg_quote( $this->GetMODxSubdirectory( FALSE, TRUE, TRUE ), '/' );

        $success = preg_match(
          '/^'
            . $modxSubdirectoryEscaped
            . '\/(([^\/]+)\/)?'
            . $aliasEscaped
            . '$/'
            . $this->itsEncodingModifier
          , $noQueryRequestURI
          , $matches
          );
        if ( $success != 1 )
        {
          $this->itsRequestLangId = $outLangId;
          $this->itsIsValidMultilingualDocument = FALSE;
          return $this->itsIsValidMultilingualDocument;
        }
        $urlRootName = $matches[2];

        $matchedLangs = array();

        // Loop over each language
        foreach ( $this->itsActiveLangIds as $langId )
        {
          // check the host name against the server name for this lang
          $serverName = $this->GetActiveServerName( $langId );
          if ( $hostName != $serverName )
          {
            continue;
          }
          // check the request URI for this lang's root name
          $rootName = $this->GetActiveRootName( $langId );
          if ( $rootName != $urlRootName )
          {
            continue;
          }
          $matchedLangs[] = $langId;
        }
        $nMatchedLangs = count( $matchedLangs );
        if ( $nMatchedLangs == 0 )
        {
          $this->itsRequestLangId = $outLangId;
          $this->itsIsValidMultilingualDocument = FALSE;
          return $this->itsIsValidMultilingualDocument;
        }
        if ( $nMatchedLangs == 1 )
        {
          $outLangId = $matchedLangs[0];
          $this->itsRequestLangId = $outLangId;
          $this->itsIsValidMultilingualDocument = TRUE;
          return $this->itsIsValidMultilingualDocument;
        }
        $this->GetDocumentIdentifierUnique( $_GET['q'], $outLangId );
        $this->itsRequestLangId = $outLangId;
        $this->itsIsValidMultilingualDocument = TRUE;
        return $this->itsIsValidMultilingualDocument;
      }
      else
      {
        // Get the host name of the request...
        $hostName = $this->GetHostName();
        // Get the request URI, without any query stuff
        $requestURI = $_SERVER['REQUEST_URI'];
        $splitRequestURI = preg_split(
          '/\?/'
            . $this->itsEncodingModifier
          , $requestURI
          );
        $noQueryRequestURI = $splitRequestURI[0];
        
        $aliasEscaped = '(index\.php|)';
        if ( isset( $_GET['q'] ) )
        {
          // Get the alias path...
          $aliasDecoded = $_GET['q'];
          // split the path into subdirectories...
          $aliasArray = preg_split(
            '/' . preg_quote( '/', '/' )  . '/'
              . $this->itsEncodingModifier
            , $aliasDecoded
            );
          // Encode each subdirectory part
          foreach ( $aliasArray as $key => $value )
          {
            $aliasArray[ $key ] = $this->UrlEncode( $value );
          }
          // Reform the encoded url and preg quote it.
          $aliasEscaped = preg_quote( implode( '/', $aliasArray ), '/' );
        }

        $modxSubdirectoryEscaped =
          preg_quote( $this->GetMODxSubdirectory( FALSE, TRUE, TRUE ), '/' );

        $success = preg_match(
          '/^'
            . $modxSubdirectoryEscaped
            . '\/(([^\/]+)\/)?'
            . $aliasEscaped
            . '$/'
            . $this->itsEncodingModifier
          , $noQueryRequestURI
          , $matches
          );
        if ( $success != 1 )
        {
          $this->itsRequestLangId = $outLangId;
          $this->itsIsValidMultilingualDocument = FALSE;
          return $this->itsIsValidMultilingualDocument;
        }
        $urlRootName = $matches[2];
        
        // Loop over each language
        foreach ( $this->itsActiveLangIds as $langId )
        {
          // check the host name against the server name for this lang
          $serverName = $this->GetActiveServerName( $langId );
          if ( $hostName != $serverName )
          {
            continue;
          }
          // check the request URI for this lang's root name
          $rootName = $this->GetActiveRootName( $langId );
          if ( $rootName != $urlRootName )
          {
            continue;
          }
          $outLangId = $langId;
          $this->itsRequestLangId = $outLangId;
          $this->itsIsValidMultilingualDocument = TRUE;
          return $this->itsIsValidMultilingualDocument;
        }
        $this->itsRequestLangId = $outLangId;
        $this->itsIsValidMultilingualDocument = FALSE;
        return $this->itsIsValidMultilingualDocument;
      }

    }

    public function GetDocumentIdentifierUnique( $q, &$langId )
    {
      // Gets the document identifier and language
      // This is to be used when the server names and root names are all
      // identical/unset AND when unique multilingual aliaes are being used
      //
      // In that case it should be possible to identify the document
      // and language from the url alone
      
      $docId = NULL;
      $langId = NULL;

      $modifier = $this->GetEncodingModifier();
      // Normalise the path:
      // 1: Replace multiple slashes by a single one...
      $path = preg_replace('/\/\/+/' . $modifier, '/', $q );
      // 2: Remove any trailing slash
      $path = preg_replace('/([^\/])\/$/' . $modifier, '$1', $path );
      // mgb: split $q on '/' to create the 'path' the the resource
      $path = preg_split('/\//' . $modifier, $path );
      // mgb: grab the 'target' alias from the end of the path
      // pms: and escape it
      $aliasDecoded = array_pop( $path );
      $alias = $this->UrlDecode( $aliasDecoded );
      $aliasEncoded = $this->UrlEncode( $aliasDecoded );

      // If no filename is specified then it must be the site start
      // document in the default language
      if ( $aliasEncoded == '' )
      {
        $langId = $this->itsDefaultLangId;
        $this->itsCurrentLangId = $langId;
        $docId = $this->itsMODx->config['site_start'];
        return $docId;
      }

      if ( $this->itsUseMimeDependentSuffixes )
      {
        $suffixMatch = '(';
        $uniqueSuffixes = array_unique( $this->itsMimeSuffixMap );
        foreach ( $uniqueSuffixes as $suffix )
        {
          // Note that the last match will be the empty string,
          // which is the default in the case of no matching mime type
          $suffixMatch .= preg_quote( $suffix, '/' ) . '|';
        }
        unset( $uniqueSuffixes );
        $suffixMatch .= ')';
      }
      else
      {
        $suffixMatch = '(' . preg_quote( $this->itsMODx->config['friendly_url_suffix'], '/' ) . ')';
      }
      
      $virtualAlias = preg_replace(
        '/^'
          . preg_quote( $this->itsMODx->config['friendly_url_prefix'], '/' )
          . '(.*?)'
          . $suffixMatch
          . '$/'
          . $this->itsEncodingModifier
        , '\1'
        , $alias
        );

      // Use a brute force approach. Try this once for each language.
      // If we get a match, then this is the correct document and language
      $docId = FALSE;
      foreach ( $this->itsDocAliases as $langId => &$docAliases )
      {
        $docId = array_search( $virtualAlias, $docAliases );
        if ( $docId === FALSE )
        {
          $docId = array_search( $alias, $docAliases );
          if ( $docId === FALSE )
          {
            continue;
          }
        }
        if ( ! $this->IsMultilingualDocument( $docId ) )
        {
          $langId = $this->itsDefaultLangId;
        }
        $this->itsCurrentLangId = $langId;
        break;
      }

      if ( $docId === FALSE )
      {

        // no match
        $langId = NULL;
        return NULL;
      }

      $path = array_reverse( $path );
      foreach ( $path as $virtualAliasDecoded )
      {
        $virtualAlias = $this->UrlDecode( $virtualAliasDecoded );
//        $virtualAliasEncoded = $this->UrlEncode( $virtualAliasDecoded );
        // This should be the virtual alias of the parent of the previous
        // document...
        $parentId = $this->itsDocParentIds[ $docId ];
        if ( $parentId == 0 )
        {
          return NULL;
        }
        $targetAlias = $this->itsDocAliases[ $langId ][ $parentId ];
        if ( $virtualAlias != $targetAlias )
        {
          
          $targetAlias = $this->itsMODx->config['friendly_url_prefix']
            . $targetAlias
            . $this->itsDocSuffixes[ $parentId ];
//            . $this->itsMODx->config['friendly_url_suffix'];
          if ( $virtualAlias != $targetAlias )
          {
            // no match
            $langId = NULL;
            return NULL;
          }
        }
        
      }
      return $docId;

    }
    
    public function GetDocumentIdentifier( $q, $langId )
    {
      $docId = NULL;

      $modifier = $this->GetEncodingModifier();
      // Normalise the path:
      // 1: Replace multiple slashes by a single one...
      $path = preg_replace('/\/\/+/' . $modifier, '/', $q );
      // 2: Remove any trailing slash
      $path = preg_replace('/([^\/])\/$/' . $modifier, '$1', $path );
      // mgb: split $q on '/' to create the 'path' the the resource
      $path = preg_split('/\//' . $modifier, $path );
      // mgb: grab the 'target' alias from the end of the path
      // pms: and escape it
      $aliasDecoded = array_pop( $path );
      $alias = $this->UrlDecode( $aliasDecoded );
      $aliasEncoded = $this->UrlEncode( $aliasDecoded );

      // Handle the case where no filename is specified.
      // This is only valid if it is the site start...
      // and, if multilingual aliases are being used,
      // only for the default language...
      if ( $aliasEncoded == '' )
      {
        if (
          $this->itsUseMultilingualAliases
          && $langId != $this->itsDefaultLangId
          )
        {
          return NULL;
        }
        $docAliasInfo = $this->GetDocumentAliasInfo(
          $this->itsMODx->config['site_start']
          , $langId
          , FALSE
          , FALSE
          , FALSE
          );
        if ( ! is_array( $docAliasInfo ) )
        {
          return NULL;
        }
        // Continue using the alias of the site start document...
        $alias = $docAliasInfo['alias'];
        $aliasEncoded = $this->UrlEncode( $alias );
      }

      if ( $this->itsUseMimeDependentSuffixes )
      {
        $suffixMatch = '(';
        $uniqueSuffixes = array_unique( $this->itsMimeSuffixMap );
        foreach ( $uniqueSuffixes as $suffix )
        {
          // Note that the last match will be the empty string,
          // which is the default in the case of no matching mime type
          $suffixMatch .= preg_quote( $suffix, '/' ) . '|';
        }
        unset( $uniqueSuffixes );
        $suffixMatch .= ')';
      }
      else
      {
        $suffixMatch = '(' . preg_quote( $this->itsMODx->config['friendly_url_suffix'], '/' ) . ')';
      }
      
      $virtualAlias = preg_replace(
        '/^'
          . preg_quote( $this->itsMODx->config['friendly_url_prefix'], '/' )
          . '(.*?)'
          . $suffixMatch
          . '$/'
          . $this->itsEncodingModifier
        , '\1'
        , $alias
        );

      // Find the matching docIds...
      $docIdsOfMatchingAliases = array(
        'virtual' => array()
        , 'standard' => array()
        );
      $nMatchingVirtualAliases = 0;
      $nMatchingStandardAliases = 0;
      foreach ( $this->itsDocAliases as $langId => &$docAliases )
      {
        // handle fact that the alias may be associated with multiple documents...
        $docIdsOfMatchingAliases['virtual'][ $langId ]
          = array_keys( $docAliases, $virtualAlias );
        $docIdsOfMatchingAliases['standard'][ $langId ]
          = array_keys( $docAliases, $alias );

        $nMatchingVirtualAliases
          += count( $docIdsOfMatchingAliases['virtual'][ $langId ] );
        $nMatchingAliases
          += count( $docIdsOfMatchingAliases['standard'][ $langId ] );
      }

      if ( $nMatchingVirtualAliases + $nMatchingAliases == 0 )
      {
        // no match
        $langId = NULL;
        return NULL;
      }

      // Loop over all matching documents...
      $path = array_reverse( $path );
      foreach ( $docIdsOfMatchingAliases as $aliasType => &$langIdDocIds )
      {
        foreach ( $langIdDocIds as $langId => &$matchingDocIds )
        {
          foreach ( $matchingDocIds as $docId )
          {
            $currentId = $docId;
            foreach ( $path as $virtualAliasDecoded )
            {
              $virtualAlias = $this->UrlDecode( $virtualAliasDecoded );
      //        $virtualAliasEncoded = $this->UrlEncode( $virtualAliasDecoded );
              // This should be the virtual alias of the parent of the previous
              // document...
              $parentId = $this->itsDocParentIds[ $currentId ];
              if ( $parentId == 0 )
              {
                continue 2;
              }
              $targetAlias = $this->itsDocAliases[ $langId ][ $parentId ];
              if ( $virtualAlias != $targetAlias )
              {
                $targetAlias = $this->itsMODx->config['friendly_url_prefix']
                  . $targetAlias
                  . $this->itsDocSuffixes[ $parentId ];
                  // . $this->itsMODx->config['friendly_url_suffix'];
                if ( $virtualAlias != $targetAlias )
                {
                  // no match
                  continue 2;
                }
              }

              $currentId = $parentId;

            }
            return $docId;
          }
        }

      }

      $langId = NULL;
      return NULL;
//      $sql = $this->BuildGenericIdentifierQuery( $virtualAlias, $langId );
//      $result = $this->itsMODx->db->query($sql);
//      $count = $this->itsMODx->recordCount($result);
//
//      $modxCharset = $this->itsMODx->config['modx_charset'];
//
//      // mgb: loop through all docIds looking up the 'target' alias
//      //      and comparing $q to the full alias of the docId
//      while ($count > 0)
//      {
//        $row = $this->itsMODx->fetchRow($result);
//        $id = $row['id'];
//        $targetEncoded = $this->GetDocumentAlias(
//          $id
//          , $langId
//          , TRUE
//          , TRUE
//          , TRUE
//          , FALSE
//        );
//        // Compare the encoded urls...
//        if (
////          mb_strtolower( $aliasEncoded, $modxCharset )
////          == mb_strtolower( $targetEncoded, $modxCharset )
//          $aliasEncoded == $targetEncoded
//        )
//        {
//          $docId = $id;
//          break;
//        }
//        $count--;
//      }
//
//      return $docId;
    }

    public function GetDocumentAlias(
      $docId
      , $langId = NULL
      , $includeSiteStartFilename = TRUE
      , $encode = TRUE
      , $filenameOnly = FALSE
      , $virtual = FALSE
      )
    {
      if ( is_null( $langId ) )
      {
        $langId = $this->itsDefaultLangId;
      }

      if ( ! $this->itsMODx->config['friendly_urls'] )
      {
        return 'index.php';
      }

      if (
        $this->itsMODx->config['use_alias_path'] == 1
        && ! $filenameOnly
        )
      {
        $path = array();
        $subdirDocId = $docId;
        $docAliasInfo = $this->GetDocumentAliasInfo(
          $subdirDocId
          , $langId
          , $encode
          , $virtual
        );
        if ( ! is_array( $docAliasInfo ) )
        {
          return FALSE;
        }
        $subdirDocId = $docAliasInfo['parent'];
        $isContainer = $docAliasInfo['container'];
        if ( $this->itsRewriteContainersAsFolders && $isContainer )
        {
          $docAliasInfo['alias'] .= '/';
        }
        $path[] = $docAliasInfo['alias'];
        while ( $subdirDocId != 0 )
        {
          $docAliasInfo = $this->GetDocumentAliasInfo(
            $subdirDocId
            , $langId
            , $encode
            , TRUE
          );
          if ( ! is_array( $docAliasInfo ) )
          {
            return FALSE;
          }
          $path[] = $docAliasInfo['alias'];
          $subdirDocId = $docAliasInfo['parent'];
        };

        if ( count( $path ) > 0 )
        {
          if ( ! $includeSiteStartFilename
            && $docId == $this->itsMODx->config['site_start']
            && ! ( $this->itsRewriteContainersAsFolders && $isContainer )
            )
          {
            if (
              ! $this->itsUseMultilingualAliases
              || (
                $this->itsUseMultilingualAliases
                && $langId == $this->itsDefaultLangId
                )
              )
            {
              $path[0] = '';
            }
          }
        }
        $alias = implode('/', array_reverse($path));
      }
      else
      {
        if ( ! $includeSiteStartFilename
          && $docId == $this->itsMODx->config['site_start']
          && ! ( $this->itsRewriteContainersAsFolders && $isContainer )
          && (
            ! $this->itsUseMultilingualAliases
            || (
              $this->itsUseMultilingualAliases
              && $langId == $this->itsDefaultLangId
              )
            )
          )
        {
          $alias = '';
        }
        else
        {
          $docAliasInfo = $this->GetDocumentAliasInfo(
            $docId
            , $langId
            , $encode
            , $virtual
            );
          if ( ! is_array( $docAliasInfo ) )
          {
            return FALSE;
          }
          $isContainer = $docAliasInfo['container'];
          if ( $this->itsRewriteContainersAsFolders && $isContainer )
          {
            $docAliasInfo['alias'] .= '/';
          }
          $alias = $docAliasInfo['alias'];
        }
      }

      return $alias;
    }

    // --
    // -- Private Stuff
    // --

    private function RedirectToCanonicalURL(
      $docId
      , $langId
      , $status = 301 )
    {
      if ( ! $this->itsMODx->config['friendly_urls'] )
      {
        return FALSE;
      }
      // Returns true if a redirection has taken place,
      // else false
      // Get the alias path...
      $aliasDecoded = $_GET['q'];
      // split the path into subdirectories...
      $aliasArray = preg_split(
        '/' . preg_quote( '/', '/' )  . '/'
          . $this->itsEncodingModifier
        , $aliasDecoded
        );
      // Encode each subdirectory part
      foreach ( $aliasArray as $key => $value )
      {
        $aliasArray[ $key ] = $this->UrlEncode( $value );
      }
      // Reform the encoded url and preg quote it.
      $aliasEscaped = implode( '/', $aliasArray );
      $targetAliasEscaped = $this->GetDocumentAlias(
          $docId
          , $langId
          , $this->itsShowSiteStartAlias
          , TRUE
          , FALSE
          , FALSE);
      if (
        ( $aliasEscaped != $targetAliasEscaped )
        || (
          // For monolingual documents, we should be matching the start of the
          // request URI. Root names are not allowed.
          ! $this->IsMultilingualDocument( $docId )
          && preg_match(
              '/^'
              . preg_quote(
                  $this->GetMODxSubdirectory( FALSE, TRUE, FALSE )
                    . '/' . $_GET['q']
                  , '/'
                )
              . '/'
              . $this->itsEncodingModifier
              , $_SERVER['REQUEST_URI']
            ) != 1
          )
        )
      {
        $url = $this->ConstructURL(
            $langId
            , $docId
            // , TRUE
            , TRUE
            , TRUE
            , TRUE
            , TRUE
            , TRUE
            , TRUE
            , FALSE
          );
        if ( $url != '' )
        {
          // error_log( 'url 2: ' . $url );
          header(
            'Location: ' . $url
            , TRUE
            , $status
          );
          exit();
          return TRUE;
        }
      }
      return FALSE;
    }

    private function CacheDocumentAliasInfo()
    {
      $sc   = $this->itsMODx->getFullTableName('site_content');
      $st   = $this->itsMODx->getFullTableName('site_tmplvars');
      $stc  = $this->itsMODx->getFullTableName('site_tmplvar_contentvalues');

      $friendlyURLSuffix = $this->itsMODx->config['friendly_url_suffix'];
      
      $this->itsDocAliases = array();
      foreach ( $this->itsActiveLangIds as $langId )
      {
        $this->itsDocAliases[ $langId ] = array();
      }
      $this->itsDocParentIds = array();
      $this->itsDocIsContainer = array();

      if ( $this->itsUseMultilingualAliases )
      {
        $aliasNameArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $aliasNameArray[] = '\'alias_' . $this->itsMODx->db->escape( $langId ) . '\'';
        }
        $aliasList = implode( ',', $aliasNameArray );
        
        $nMonoDocs = count( $this->itsMonolingualDocIds );
        if ( $nMonoDocs > 0 )
        {
          // Create a list of monolingual document ids...
          $monoDocList = implode( ',', $this->itsMonolingualDocIds );
          // Get the parents and aliases of the monolingual documents...
          $monoSQL =
            '(SELECT sc.parent, sc.contentType, sc.isfolder, sc.alias, sc.id'
            . ' FROM ' . $sc . ' sc'
            . ' WHERE sc.id IN (' . $monoDocList . ')'
            . ' AND sc.deleted = 0)';
          $result = $this->itsMODx->db->query( $monoSQL );
          $nDocs = $this->itsMODx->recordCount($result);
          for ( $i = 0; $i<$nDocs; $i++ )
          {
            $aliasInfo = $this->itsMODx->fetchRow($result);
            $docId = &$aliasInfo[ 'id' ];
            $parentId = &$aliasInfo[ 'parent' ];
            $contentType = &$aliasInfo[ 'contentType' ];
            $isContainer = &$aliasInfo[ 'isfolder' ];
            $alias = &$aliasInfo[ 'alias' ];
            if ( $alias == '' )
            {
              $alias = $docId;
            }
            $this->itsDocParentIds[ $docId ] = $parentId;
            $this->itsDocSuffixes[ $docId ] = $friendlyURLSuffix;
            if (
              $this->itsUseMimeDependentSuffixes
              && array_key_exists( $contentType, $this->itsMimeSuffixMap )
              )
            {
              $this->itsDocSuffixes[ $docId ] = $this->itsMimeSuffixMap[ $contentType ];
            }
            $this->itsDocIsContainer[ $docId ] = $isContainer;
            foreach ( $this->itsDocAliases as &$aliasArray )
            {
              $aliasArray[ $docId ] = $alias;
            }
          }
          $multiSQL =
            '(SELECT'
              . ' sc.parent'
              . ', sc.contentType'
              . ', sc.isfolder'
              . ', st.name'
              . ', stc.value as alias'
              . ', sc.id'
            . ' FROM '
              . $st . ' st'
            . ' LEFT JOIN ' . $stc . ' stc'
              . ' ON st.id = stc.tmplvarid'
            . ' INNER JOIN ' . $sc . ' sc'
              . ' ON stc.contentid = sc.id'
            . ' WHERE'
              . ' st.name IN (' . $aliasList . ')'
              . ' AND sc.id NOT IN (' . $monoDocList . ')'
              . ' AND sc.deleted = 0'
            . ')';
        }
        else
        {
          // All the docs are multilingual...
          $multiSQL =
            '(SELECT'
              . ' sc.parent'
              . ', sc.contentType'
              . ', sc.isfolder'
              . ', st.name'
              . ', stc.value as alias'
              . ', sc.id'
            . ' FROM '
              . $st . ' st'
            . ' INNER JOIN ' . $stc . ' stc'
              . ' ON st.id = stc.tmplvarid'
            . ' INNER JOIN ' . $sc . ' sc'
              . ' ON stc.contentid = sc.id'
            . ' WHERE'
              . ' st.name IN (' . $aliasList . ')'
              . ' AND sc.deleted = 0'
            . ')';
        }
        $result = $this->itsMODx->db->query( $multiSQL );
        $nResults = $this->itsMODx->recordCount( $result );
        for ( $i = 0; $i<$nResults; $i++ )
        {
          // This is an array containing 'alias' and 'parent' and 'isfolder'
          $aliasInfo = $this->itsMODx->fetchRow($result);
          $docId = &$aliasInfo[ 'id' ];
          $parentId = &$aliasInfo[ 'parent' ];
          $contentType = &$aliasInfo[ 'contentType' ];
          $isContainer = &$aliasInfo[ 'isfolder' ];
          $alias = &$aliasInfo[ 'alias' ];
          $name = &$aliasInfo[ 'name' ];

          $this->itsDocParentIds[ $docId ] = $parentId;
          $this->itsDocSuffixes[ $docId ] = $friendlyURLSuffix;
          if (
            $this->itsUseMimeDependentSuffixes
            && array_key_exists( $contentType, $this->itsMimeSuffixMap )
            )
          {
            $this->itsDocSuffixes[ $docId ] = $this->itsMimeSuffixMap[ $contentType ];
          }
          $this->itsDocIsContainer[ $docId ] = $isContainer;
          foreach ( $this->itsDocAliases as $langId => &$aliasArray )
          {
            if ( $alias == '' )
            {
              if ( $this->itsUseUniqueMultilingualAliases )
              {
                $alias = $langId . '-' . $docId;
              }
              else
              {
                $alias = $docId;
              }
            }
            if ( $name == 'alias_' . $langId )
            {
              // TODO
              // If this language has been disabled for this document, then set
              // NULL else set the alias
              $aliasArray[ $docId ] = $alias;
            }
          }
        }
      }
      else
      {
        $sql =
          'SELECT sc.parent, sc.contentType, sc.isfolder, sc.alias, sc.id'
          . ' FROM ' . $sc . ' sc'
          . ' WHERE sc.deleted = 0';
        $result = $this->itsMODx->db->query( $sql );
        $nDocs = $this->itsMODx->recordCount($result);
        for ( $i = 0; $i<$nDocs; $i++ )
        {
          // This is an array containing 'alias' and 'parent' and 'isfolder'
          $aliasInfo = $this->itsMODx->fetchRow($result);
          $docId = &$aliasInfo[ 'id' ];
          $parentId = &$aliasInfo[ 'parent' ];
          $contentType = &$aliasInfo[ 'contentType' ];
          $isContainer = &$aliasInfo[ 'isfolder' ];
          $alias = &$aliasInfo[ 'alias' ];
          if ( $alias == '' )
          {
            $alias = $docId;
          }
          $this->itsDocParentIds[ $docId ] = $parentId;
          $this->itsDocSuffixes[ $docId ] = $friendlyURLSuffix;
          if (
            $this->itsUseMimeDependentSuffixes
            && array_key_exists( $contentType, $this->itsMimeSuffixMap )
            )
          {
            $this->itsDocSuffixes[ $docId ] = $this->itsMimeSuffixMap[ $contentType ];
          }
          $this->itsDocIsContainer[ $docId ] = $isContainer;
          foreach ( $this->itsDocAliases as &$aliasArray )
          {
            $aliasArray[ $docId ] = $alias;
          }
        }
      }
      
    }

    private function GetDocumentAliasInfo(
      $docId
      , $langId = NULL
      , $encode = TRUE
      , $virtual = FALSE
      )
    {
      if ( is_null( $langId ) )
      {
        $langId = $this->itsDefaultLangId;
      }
      if ( ! array_key_exists( $langId, $this->itsDocAliases ) )
      {
        return FALSE;
      }
      if ( ! array_key_exists( $docId, $this->itsDocAliases[ $langId ] ) )
      {
        return FALSE;
      }
      if ( ! array_key_exists( $docId, $this->itsDocParentIds ) )
      {
        return FALSE;
      }
      if ( ! array_key_exists( $docId, $this->itsDocIsContainer ) )
      {
        return FALSE;
      }
      $aliasInfo = array(
        'alias' => $this->itsDocAliases[ $langId ][ $docId ]
        , 'parent' => $this->itsDocParentIds[ $docId ]
        , 'container' => $this->itsDocIsContainer[ $docId ]
        );
      if (
        ! $virtual
        &&
        ! ( $this->itsRewriteContainersAsFolders && $aliasInfo['container'] )
      )
      {
        $prefix = $this->itsMODx->config['friendly_url_prefix'];
        // $suffix = $this->itsMODx->config['friendly_url_suffix'];
        $suffix = $this->itsDocSuffixes[ $docId ];
        $aliasInfo['alias'] =
          $prefix
          . $aliasInfo['alias']
          . $suffix;
      }
      if ( $encode )
      {
        $aliasInfo['alias'] = $this->UrlEncode( $aliasInfo['alias'] );
      }
      return $aliasInfo;
    }

    //----------------------------------------------------------------------
    // End YAMS_UX
    //----------------------------------------------------------------------

    private function UpdateMonolingualDocIds( )
    {
      $sc = $this->itsMODx->getFullTableName('site_content');

      $activeTemplatesList = $this->GetActiveTemplatesList();

      if ( $activeTemplatesList != '' )
      {
        $activeTemplateList =
          ' template NOT IN ('
            . $activeTemplatesList
          . ')';
      }
      else
      {
        $activeTemplateList = '';
      }

      $result = $this->itsMODx->db->select(
        'id'
        , $sc
        , $activeTemplateList
        );
      $this->itsMonolingualDocIds = $this->itsMODx->db->getColumn( 'id', $result );
      
    }

//    private function BuildMonolingualContentQuery( $contentCache )
//    {
//      $sc = $this->itsMODx->getFullTableName('site_content');
//
//      if ( count( $this->itsActiveTemplates ) > 0 )
//      {
//        $activeTemplateList =
//          ' WHERE scall.template NOT IN ('
//          . implode( ',', $this->itsActiveTemplates )
//          . ')';
//      }
//      else
//      {
//        $activeTemplateList = '';
//      }
//
//      $sql = array();
//      foreach ( $contentCache as $docId => $fields )
//      {
//        if ( count( $fields ) == 0 )
//        {
//          continue;
//        }
//
//        $fieldArray = array_keys( $fields );
//        foreach ( $fieldArray as $name => $value )
//        {
//          $fieldArray[ $name ] = 'sc.' . $this->itsMODx->db->escape( $value );
//        }
//        $fieldList = implode(',', $fieldArray );
//        $sql[] =
//          'SELECT sc.id, ' . $fieldList . ', 0 AS multilingual'
//          . ' FROM (SELECT * FROM ' . $sc . ' scall ' . $activeTemplateList . ' )' . $sc . ' sc'
//          . ' WHERE sc.id = ' . $this->itsMODx->db->escape( $docId )
//          . $activeTemplateList;
//      }
//      return implode( ' UNION ', $sql );
//    }
//
//    private function BuildMultilingualContentQuery( $contentCache )
//    {
//      $sc   = $this->itsMODx->getFullTableName('site_content');
//      $st   = $this->itsMODx->getFullTableName('site_tmplvars');
//      $stc  = $this->itsMODx->getFullTableName('site_tmplvar_contentvalues');
//
//      if ( count( $this->itsActiveTemplates ) > 0 )
//      {
//        $activeTemplateList =
//          ' WHERE scall.template IN ('
//          . implode( ',', $this->itsActiveTemplates )
//          . ')';
//      }
//      else
//      {
//        $activeTemplateList = '';
//      }
//
//      $fieldList = array();
//      foreach ( $contentCache as $docId => $fields )
//      {
//        if ( count( $fields ) == 0 )
//        {
//          continue;
//        }
//
//        $fieldArray = array_keys( $fields );
//        $fieldQueries = array();
//        foreach ( $this->itsActiveLangIds as $langId )
//        {
//          foreach ( $fieldArray as $name => $value )
//          {
//            $fieldQueries[] =
//              'st.name=\''
//                . $this->itsMODx->db->escape( $value )
//                . '_'
//                . $this->itsMODx->db->escape( $langId )
//                . '\'';
//          }
//        }
//        $fieldList[] = 'sc.id=' .  $docId . ' AND (' . implode(' OR ', $fieldQueries ) . ')';
//      }
//      $fieldQuery = ' AND ' . implode( ' OR ' . $fieldList );
//
//
//      // $lang = $this->GetCurrentLangId();
//      $aliasName = $this->itsMODx->db->escape( 'alias_' . $langId );
//      return
//        'SELECT sc.id, stc.name AS name, stc.value AS value, 1 AS multilingual'
//        . ' FROM (SELECT * FROM ' . $sc . ' scall ' . $activeTemplateList . ' ) sc, ' . $st . ' st, ' . $stc . ' stc'
//        . ' WHERE sc.id = stc.contentid'
//        . ' AND st.id = stc.tmplvarid'
//      	. $fieldQuery;
//    }

//    Redundant now...
//    private function RemoveSiteStartAlias( $url, $docId )
//    {
//      if ( $this->itsShowSiteStartAlias )
//      {
//        return $url;
//      }
//      if ( $docId != $this->itsMODx->config['site_start'] )
//      {
//        return $url;
//      }
//      return preg_replace(
//          '/^(.*\/)[^\/]+?$/D'
//          . $this->itsEncodingModifier
//        , '$1'
//        , $url
//        );
//
//    }
//
    private function GetDocURL( $docId, $langId )
    {
      return $this->ConstructURL(
          $langId
          , $docId
          // , FALSE
          , TRUE
          , TRUE
          , TRUE
          , FALSE
          , TRUE
          , FALSE
          , TRUE
        );
    }

    public function ConstructResolvedURL(
      $langId
      , $docId
      // , $includeRequestURI = TRUE
      , $includeRootName = TRUE
      , $includeTrailingSlash = TRUE
      , $includeVirtualPath = TRUE
      , $includeGetParams = TRUE
      , $includeQueryParam = TRUE
      , $stripChangeLangQueryParam = FALSE
      , $isHTMLOutput = TRUE
      , &$seenDocIds = array()
      )
    {
      // Gets the full URL of a document, but resolves weblinks.
      $docInfo = $this->itsMODx->getPageInfo(
        $docId
        , 0
        , 'content,type,template');
      $isMultilingualDocument =
        $this->IsMultilingualDocument( $docId );
      $output = '';
      if ( $docInfo[ 'type' ] == 'reference' )
      {
        if ( $isMultilingualDocument )
        {
          $contentName = 'content_' . $langId;

          $multiDocInfo = $this->itsMODx->getTemplateVarOutput(
            array( $contentName )
            , $docId
            , 1
            );
          $newDocId = $multiDocInfo[ $contentName ];
          if ( $this->IsValidId( $newDocId ) )
          {
            // This is a link to another internal document
            // Check if we have already seen it to prevent infinite recursion
            if ( in_array( $newDocId, $seenDocIds ) )
            {
              $link = '';
            }
            else
            {
              array_push( $seenDocIds, $newDocId );
              $link =
                $this->ConstructResolvedURL(
                  $langId
                  , $newDocId
                  // , $includeRequestURI
                  , $includeRootName
                  , $includeTrailingSlash
                  , $includeVirtualPath
                  , $includeGetParams
                  , $includeQueryParam
                  , $stripChangeLangQueryParam
                  , $isHTMLOutput
                  , $seenDocIds
                  );
            }
          }
          else
          {
            // return the URL of the weblink
            $link = $newDocId;
          }
          $output = $link;
        }
        else
        {
          $newDocId = $docInfo[ 'content' ];
          if ( $this->IsValidId( $newDocId ) )
          {
            // This is a link to another internal document
            // Check if we have already seen it to prevent infinite recursion
            if ( in_array( $newDocId, $seenDocIds ) )
            {
              $output = '';
            }
            else
            {
              array_push( $seenDocIds, $newDocId );
              $output =
                $this->ConstructResolvedURL(
                  $langId
                  , $newDocId
                  // , $includeRequestURI
                  , $includeRootName
                  , $includeTrailingSlash
                  , $includeVirtualPath
                  , $includeGetParams
                  , $includeQueryParam
                  , $stripChangeLangQueryParam
                  , $isHTMLOutput
                  , $seenDocIds
                  );
            }
          }
          else
          {
            // return the URL of the weblink
            $output = $newDocId;
          }
        }
      }
      else
      {
        if ( $isMultilingualDocument )
        {
          if ( is_null( $langId ) )
          {
            $langId = $this->itsDefaultLangId;
          }
          $link = 
            $this->ConstructURL(
              $langId
              , $docId
              // , $includeRequestURI
              , $includeRootName
              , $includeTrailingSlash
              , $includeVirtualPath
              , $includeGetParams
              , $includeQueryParam
              , $stripChangeLangQueryParam
              , $isHTMLOutput
              );
          $output = $link;
        }
        else
        {
          $output = 
            $this->ConstructURL(
              NULL
              , $docId
              // , $includeRequestURI
              , $includeRootName
              , $includeTrailingSlash
              , $includeVirtualPath
              , $includeGetParams
              , $includeQueryParam
              , $stripChangeLangQueryParam
              , $isHTMLOutput
              );
        }
      }

      return $output;
    }

    private function GetDocResolvedURL(
      $docId
      , $mode = ''
      , &$seenDocIds = array()
      )
    {
      // Gets the full URL of a document, but resolves weblinks.
      $docInfo = $this->itsMODx->getPageInfo(
        $docId
        , 0
        , 'content,type,template');
      $isMultilingualDocument =
        $this->IsMultilingualDocument(
          $docId
//          , $docInfo['template']
        );
      $output = '';
      if ( $docInfo[ 'type' ] == 'reference' )
      {
        if ( $isMultilingualDocument )
        {
          $contentArray = array();
          foreach ( $this->itsActiveLangIds as $langId )
          {
            $contentArray[ $langId ] = 'content_' . $langId;
          }

          $multiDocInfo = $this->itsMODx->getTemplateVarOutput(
            $contentArray
            , $docId
            , 1);
          $expandArray = array();
          foreach ( $this->itsActiveLangIds as $langId )
          {
            $newDocId = $multiDocInfo[ $contentArray[ $langId ] ];
            if ( $this->IsValidId( $newDocId ) )
            {
              // This is a link to another internal document
              // Check if we have already seen it to prevent infinite recursion
              if ( in_array( $newDocId, $seenDocIds ) )
              {
                $expandArray[ $langId ] = '';
              }
              else
              {
                array_push( $seenDocIds, $newDocId );
                $expandArray[ $langId ] =
                  $this->GetDocResolvedURL(
                    $newDocId
                    , $mode
                    , $seenDocIds
                    );
              }
            }
            else
            {
              // return the URL of the weblink
              $expandArray[ $langId ] = $newDocId;
            }
          }
          $output = $this->Expand(
            'text'
            , $expandArray
            , NULL
            , $mode
            );
        }
        else
        {
          $newDocId = $docInfo[ 'content' ];
          if ( $this->IsValidId( $newDocId ) )
          {
            // This is a link to another internal document
            // Check if we have already seen it to prevent infinite recursion
            if ( in_array( $newDocId, $seenDocIds ) )
            {
              $output = '';
            }
            else
            {
              array_push( $seenDocIds, $newDocId );
              $output = $this->GetDocResolvedURL(
                $newDocId
                , $mode
                , $seenDocIds
                );
            }
          }
          else
          {
            // return the URL of the weblink
            $output = $newDocId;
          }
        }
      }
      else
      {
        if ( $isMultilingualDocument )
        {
          $expandArray = array();
          foreach ( $this->itsActiveLangIds as $langId )
          {
            $expandArray[ $langId ]
              = $this->GetDocURL( $docId, $langId );
          }
          $output = $this->Expand(
            'text'
            , $expandArray
            , NULL
            , $mode
            );
        }
        else
        {
          $output
            = $this->GetDocURL( $docId, NULL );
        }
      }

      return $output;
    }

    private function UpdateLanguageDependentServerNamesMode()
    {
      // If language dependent server names can be used, then
      // do so
      $this->itsUseLanguageDependentServerNames =
        $this->CanUseLanguageDependentServerNames();
      return TRUE;
    }

    private function UpdateLanguageDependentRootNamesMode()
    {
      // If language dependent server names can be used, then
      // do so
      $this->itsUseLanguageDependentRootNames =
        $this->IsUsingLanguageDependentRootNames();
      return TRUE;
    }

    private function UpdateLanguageQueryParamMode()
    {
      // If the language can be determine from the multilingual
      // aliases, then switch off query param mode
      if (
        $this->itsUseMultilingualAliases
        && $this->itsMultilingualAliasesAreUnique )
      {
        $this->itsUseLanguageQueryParam = FALSE;
        return TRUE;
      }
      // If language dependent server names or root names can be used
      // to determine the language, then do that.
      // do so
      if ( $this->ActiveURLsAreUnique() )
      {
        $this->itsUseLanguageQueryParam = FALSE;
        return TRUE;
      }
      // Fallback on a query parameter to determine the language
      $this->itsUseLanguageQueryParam = TRUE;
      return TRUE;
    }

    private function UpdateUniqueMultilingualAliasMode()
    {
      // If the language can be determined uniquely from the
      // alias, turn this mode on.
      if (
        $this->itsUseMultilingualAliases
        && $this->itsMultilingualAliasesAreUnique )
      {
        $this->itsUseUniqueMultilingualAliases = TRUE;
      }
      else
      {
        $this->itsUseUniqueMultilingualAliases = FALSE;
      }
      return TRUE;
    }

    private function IsUsingLanguageDependentRootNames()
    {
      // If any are set and non-empty, then yes
      foreach ( $this->itsActiveLangIds as $langId )
      {
        if ( ! array_key_exists( $langId, $this->itsRootName ) )
        {
          continue;
        }
        if ( $this->itsRootName[ $langId ] != '' )
        {
          return TRUE;
        }
      }
      return FALSE;
    }

    private function GetBrowserLangId()
    {
      if ( !isset( $_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] ) )
      {
        return $this->itsDefaultLangId;
      }

      // Based on http://www.thefutureoftheweb.com/blog/use-accept-header
      preg_match_all(
        '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i'
        , $_SERVER['HTTP_ACCEPT_LANGUAGE']
        , $parsedAcceptLanguageHeader
      );

      $langTags = array();

      if ( count( $parsedAcceptLanguageHeader[1] ) > 0 )
      {
        // create a list like 'en' => 0.8
        $langTags = array_combine(
          $parsedAcceptLanguageHeader[1]
          , $parsedAcceptLanguageHeader[4]
        );

        // set default to 1 for any without q factor
        // For any with 1 or without a q factor (effective 1)
        // count down from a very large number to
        // ensure the correct sort order...
        $oneSort = 20000000;
        foreach ( $langTags as $langTag => $val )
        {
          if ( $val == 1 || $val === '' )
          {
            $oneSort -= 1;
            $langTags[ $langTag ] = $oneSort;
          }

          // sort list based on value
          arsort( $langTags, SORT_NUMERIC );
        }
      }

      // look through sorted list
      // use first one that matches our languages
      foreach ( $langTags as $langTag => $val )
      {
        foreach ( $this->itsLangTags as $langId => $tagArray )
        {
          if ( ! $this->IsActiveLangId( $langId ) )
          {
            continue;
          }
          if ( ! is_array( $tagArray ) )
          {
            continue;
          }

          foreach ( $tagArray as $tag )
          {

//            if ( strpos( $langTag, $tag ) === 0 )
//            {
//              return $langId;
//            }
            if ( preg_match(
                '/^'  . preg_quote( $tag, '/' ) . '/'
                  . $this->itsEncodingModifier
                , $langTag ) == 1 )
            {
              return $langId;
            }
          }
        }
      }
      return $this->itsDefaultLangId;
    }

    private function GetHostName()
    {
      // Get the host name of the request...
      $hostName = $_SERVER['HTTP_HOST'];
      $stripPort =
        ( $_SERVER['SERVER_PORT'] != 80 )
        && ( ! $this->IsHTTPS() );
      if ( $stripPort )
      {
        // Strip the port
        $hostName = preg_replace(
          '/' . preg_quote( ':' . $_SERVER['SERVER_PORT'], '/' ) . '/'
            . $this->itsEncodingModifier
          , ''
          , $hostName
          );
      }
      return $hostName;
    }

    private function GetServerNameAndRoot( $langId, $encoded = TRUE )
    {
      if (
        ! $this->IsActiveLangId( $langId )
        && ! $this->IsInactiveLangId( $langId ))
      {
        return '';
      }
      // $stripPort =
        // ( $_SERVER['SERVER_PORT'] != 80 )
        // && ( ! $this->IsHTTPS() );
      $serverName = $this->GetActiveServerName(
        $langId
        // , $stripPort
        );
      $rootName = $this->GetActiveRootName( $langId, $encoded );
      if ( $rootName != '' )
      {
        $rootName = '/' . $rootName;
      }
      return $serverName . $rootName;
    }

    private function RemoveActiveLangId(
      $langId
      , $save = FALSE
      )
    {
      if ( ! $this->IsActiveLangId( $langId ) )
      {
        return FALSE;
      }
      if ( $this->itsDefaultLangId == $langId )
      {
        return FALSE;
      }
      $this->itsActiveLangIds =
        array_values(
          array_diff(
            $this->itsActiveLangIds
            , array( $langId )
          )
        );
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    private function RemoveInactiveLangId( $langId, $save = FALSE )
    {
      if ( ! $this->IsInactiveLangId( $langId ) )
      {
        return FALSE;
      }
      $this->itsInactiveLangIds =
        array_values(
          array_diff(
            $this->itsInactiveLangIds
            , array( $langId )
          )
        );
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    private function RemoveLangTagsText( $langId, $save = FALSE )
    {
      if (
        $this->IsInactiveLangId( $langId )
        || $this->IsActiveLangId( $langId )
        )
      {
        return FALSE;
      }
      if ( array_key_exists( $langId, $this->itsLangTags ) )
      {
        unset( $this->itsLangTags[ $langId ] );
      }
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    private function RemoveRootName( $langId, $save = FALSE )
    {
      if (
        $this->IsInactiveLangId( $langId )
        || $this->IsActiveLangId( $langId )
        )
      {
        return FALSE;
      }
      if ( array_key_exists( $langId, $this->itsRootName ) )
      {
        unset( $this->itsRootName[ $langId ] );
      }
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    private function RemoveServerName( $langId, $save = FALSE )
    {
      if (
        $this->IsInactiveLangId( $langId )
        || $this->IsActiveLangId( $langId )
        )
      {
        return FALSE;
      }
      if ( array_key_exists( $langId, $this->itsMultiServerName ) )
      {
        unset( $this->itsMultiServerName[ $langId ] );
      }
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    private function RemoveIsLTR( $langId, $save = FALSE )
    {
      if (
        $this->IsInactiveLangId( $langId )
        || $this->IsActiveLangId( $langId )
        )
      {
        return FALSE;
      }
      if ( array_key_exists( $langId, $this->itsIsLTR ) )
      {
        unset( $this->itsIsLTR[ $langId ] );
      }
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    private function RemoveChooseLangText( $langId, $save = FALSE )
    {
      if (
        $this->IsInactiveLangId( $langId )
        || $this->IsActiveLangId( $langId )
        )
      {
        return FALSE;
      }
      if ( array_key_exists( $langId, $this->itsChooseLangText ) )
      {
        unset( $this->itsChooseLangText[ $langId ] );
      }
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    private function RemoveMODxLangName( $langId, $save = FALSE )
    {
      if (
        $this->IsInactiveLangId( $langId )
        || $this->IsActiveLangId( $langId )
        )
      {
        return FALSE;
      }
      if ( array_key_exists( $langId, $this->itsMODxLangName ) )
      {
        unset( $this->itsMODxLangName[ $langId ] );
      }
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    public function SelectOneLangFromCache( &$content )
    {
      // Strip out unwanted language variants...
      // if the document has come from the cache
      if ( $this->itsFromCache )
      {
        $content
          = preg_replace_callback(
            '/^(?>\(yams-select(|\+):([0-9]{1,10})\))'
            . '(.*)'
            . '\(\/yams-select\1:\2\)$/s'
            . $this->itsEncodingModifier
            , array($this, 'SelectLangCallback')
            , $content
            , 1
            , $count
          );
        if ( $count > 0 && $content != '' )
        {
          $yamsCounter = $this->GetYamsCounter();
          $content = '(yams-select:' . $yamsCounter . ')'
            . '(lang:' . $yamsCounter . ':' . $this->itsParseLangId . ')'
            . $content
            . '(/yams-select:' . $yamsCounter . ')';
        }
      }
      
    }

    private function RemoveLangNames( $inlangId, $save = FALSE )
    {
      if (
        $this->IsInactiveLangId( $inlangId )
        || $this->IsActiveLangId( $inlangId )
        )
      {
        return FALSE;
      }
      if ( array_key_exists( $inlangId, $this->itsLangNames ) )
      {
        unset( $this->itsLangNames[ $inlangId ] );
      }
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    private function AddActiveLangId(
      $langId
      , $save = FALSE
    )
    {
      if ( !is_string( $langId ) || ! ctype_graph( $langId ) )
      {
        return FALSE;
      }
      if ( $this->IsActiveLangId( $langId ) )
      {
        return FALSE;
      }
      $this->itsActiveLangIds[] = $langId;
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

    private function AddInactiveLangId(
      $langId
      , $save = FALSE
    )
    {
      if ( !is_string( $langId ) || ! ctype_graph( $langId ) )
      {
        return FALSE;
      }
      if ( $this->IsInactiveLangId( $langId ) )
      {
        return FALSE;
      }
      $this->itsInactiveLangIds[] = $langId;
      if ( $save )
      {
        return $this->SaveCurrentSettings();
      }
      return TRUE;
    }

//    private function GetLanguageList(
//      $listOpenTpl = NULL
//      , $listCloseTpl = NULL
//      , $listItemCurrentTpl = NULL
//      , $listItemOtherTpl = NULL
//      )
//    {
//
//      if ( ! is_string( $listOpenTpl ) )
//      {
//        $listOpenTpl = '<ul>';
//      }
//
//      if ( ! is_string( $listCloseTpl ) )
//      {
//        $listCloseTpl = '</ul>';
//      }
//
//      $output = $listOpenTpl;
//      $tagCurrent = $this->GetPrimaryLangTag( $this->itsCurrentLangId );
//      foreach ( $this->itsActiveLangIds as $langId )
//      {
//        $nameNative = $this->GetLangName( $langId );
//        $nameCurrent = $this->GetLangName( $this->itsCurrentLangId, $langId );
//        $tagNative = $this->GetPrimaryLangTag( $langId );
//        $langDirNative = $this->GetLangDir( $langId );
//        $output .=
//          '<li>';
//        if ( $langId == $this->itsCurrentLangId )
//        {
//          $output .= $nameCurrent;
//        }
//        else
//        {
//          $output .=
//            '<a href="'
//            . $this->ConstructURL(
//                $langId
//                , FALSE
//                , TRUE
//                , TRUE
//                , TRUE )
//            . '">'
//            . '<span xml:lang="' . $tagNative . '" lang="' . $tagNative . '" dir="' . $langDirNative . '">' . $nameNative . '</span> '
//            . $nameCurrent
//            . '</a>';
//        }
//        $output .= '</li>';
//      }
//      $output .= $listCloseTpl;
//      return $output;
//
//    }
//
//    private function GetLanguageSelect()
//    {
//
//      $tagCurrent = $this->GetPrimaryLangTag( $this->itsCurrentLangId );
//
//      $output = '<label for="yams_lang_select">'
//      . '<select id="yams_lang_select" name="'
//      . htmlspecialchars( $this->itsChangeLangQueryParam )
//      . '" onchange="javascript:this.form.submit();">';
//      foreach ( $this->itsActiveLangIds as $langId )
//      {
//        $nameNative = $this->GetLangName( $langId );
//        $nameCurrent = $this->GetLangName( $this->itsCurrentLangId, $langId );
//        $tagNative = $this->GetPrimaryLangTag( $langId );
//        $langDirNative = $this->GetLangDir( $langId );
//
//        $selected = '';
//        if ( $this->itsCurrentLangId == $langId )
//        {
//          $selected = 'selected="selected"';
//          $output .=
//            '<option xml:lang="' . $tagNative . '" lang="' . $tagNative . '" dir="' . $langDirNative . '" value="' . $langId . '" ' . $selected . '>'
//            . $nameCurrent
//            . '</option>';
//        }
//        else
//        {
//          $output .=
//            '<option xml:lang="' . $tagNative . '" lang="' . $tagNative . '" dir="' . $langDirNative . '" value="' . $langId . '" ' . $selected . '>'
//            . $nameNative
//            . ' '
//            . $nameCurrent
//            . '</option>';
//        }
//      }
//      $chooseLangText = htmlspecialchars(
//        $this->GetChooseLangText(
//          $this->itsCurrentLangId
//          )
//        );
//      $output .=
//        '</select>'
//        . '<input type="image" src="[(site_url)]assets/modules/yams/languageicons/langiconclassic_r9_c22.png" alt="' . $chooseLangText . '" title="' . $chooseLangText . '" />'
//        . '</label>';
//      return $output;
//
//    }
//
//    private function GetLanguageSelectForm( )
//    {
//      $output =
//        '<form action="(yams_doc:' . $this->itsMODx->documentIdentifier . ')" method="post">'
//        . '<fieldset>'
//        . $this->GetLanguageSelect()
//        . '</fieldset>'
//        . '</form>';
//      return $output;
//    }

    private function GetYamsCounter()
    {
      return $this->itsYamsCounter++;
    }

    public function Expand(
      $get
      , $from
      , $docId = NULL
      , $mode = ''
      , $beforeModifier = ''
      , $afterModifier = ''
    )
    {

      // Determine which brackets to use depending on the action...
      switch( $get )
      {
      case 'text':
        $open = '';
        $close = '';
        break;
      case 'chunk':
      case '{{':
        $open = '{{';
        $close = '}}';
        break;
      case 'csnippet':
      case '[[':
        $open = '[[';
        $close = ']]';
        break;
      case 'usnippet':
      case '[!':
        $open = '[!';
        $close = '!]';
        break;
      case 'placeholder':
      case '[+':
        $open = '[+';
        $close = '+]';
        break;
      case 'tv':
      case '[*':
        $open = '[*';
        $close = '*]';
        break;
      case 'data':
      case 'content':
        if ( is_null( $docId ) )
        {
          $open = '((yams_data:';
          $close = '))';
        }
        else if ( $this->IsValidId( $docId ) )
        {
          $open = '((yams_data:' . $docId . ':';
          $close = '))';
        }
        else
        {
          return '';
        }
        break;
      default:
        return '';
      }

      if ( is_string( $from ) )
      {
        // Parse the select argument and create a lang array
        $langArray = array();

        $langNameArray = preg_split(
          '/\|\|/'
            . $this->itsEncodingModifier
          , $from
          , -1
          );
        unset( $from );
        foreach ( $langNameArray as $langName )
        {
          $result = preg_match(
            '/^([a-zA-Z0-9]+)::(.*?)$/DU'
              . $this->itsEncodingModifier
            , $langName
            , $matches
            );
          if ( $result != 1 )
          {
            continue;
          }
          $langId = $matches[1];
          $name = $matches[2];
          if ( $this->IsActiveLangId( $langId ) )
          {
            $langArray[ $langId ] = $name;
          }
        }
      }
      elseif ( is_array( $from ) )
      {
        $langArray = &$from;
//        foreach ( $langArray as $langId => $name )
//        {
//          if ( ! $this->IsActiveLangId( $langId ) )
//          {
//            unset( $langArray[ $langId ] );
//            continue;
//          }
//          if ( !is_string( $name ) )
//          {
//            unset( $langArray[ $langId ] );
//            continue;
//          }
//        }
////        if ( ! array_key_exists( $this->itsDefaultLangId, $langArray ) )
////        {
////          return '';
////        }
      }
      else
      {
        return '';
      }

      // Get an id for this yams multilingual block
      $yamsCounter = $this->GetYamsCounter();

      // Build the multilingual output...
      // Start with the default language
      $startBlock =
        '(yams-select' . $mode . ':' . $yamsCounter . ')';
      // Add the other languages...
      $output = '';
      foreach ( $langArray as $langId => $name )
      {
        if ( ! in_array( $langId, $this->itsActiveLangIds ) )
        {
          continue;
        }
        $content = $open
          . $beforeModifier
          . $name
          . $afterModifier
          . $close;
        if ( $content == '' )
        {
          continue;
        }
        $output .=
          '(lang:' . $yamsCounter . ':' . $langId . ')'
          . $content;
      }
      if ( $output == '' )
      {
        return '';
      }
      // Add the closing tag
      $endBlock =
        '(/yams-select' . $mode . ':' . $yamsCounter . ')';

      return $startBlock . $output . $endBlock;

    }

    private function MergeChunkContent( &$content )
    {
      // Returns true if the content has been changed.
      //
      // This is similar to the MODx function, except
      // - it performs fewer sql queries
      // - it doesn't delete chunks that have not been recognised.
      
      $find = array();
      $replace= array();
      $fromDB = array();
      $matches= array();
      
      $nMatches = preg_match_all(
        '/\{\{(.+)\}\}/U'
          . $this->itsEncodingModifier
        , $content
        , $matches
        );
      if ( $nMatches > 0 )
      {
        for ( $i = 0; $i < $nMatches; $i++ )
        {
          $chunkName = $matches[1][$i];
          if ( isset( $this->chunkCache[ $chunkName ] ) )
          {
            $find[] =
              '/' . preg_quote( $matches[0][$i], '/' ) . '/'
              . $this->itsEncodingModifier;
            $replace[]= $this->chunkCache[ $chunkName ];
          }
          else
          {
            $fromDB[] = '\'' . $this->itsMODx->db->escape( $chunkName ) . '\'';
          }
        }
        unset( $matches );
        if ( count( $fromDB ) > 0 )
        {
          $chunkNamesList = implode( ',', $fromDB );
          unset( $fromDB );
          $tableName = $this->itsMODx->getFullTableName('site_htmlsnippets');
          $sql= 'SELECT name,snippet'
           . ' FROM ' . $tableName
           . ' WHERE name IN (' . $chunkNamesList . ');';
          $result = $this->itsMODx->db->query( $sql );
          $nChunks = $this->itsMODx->recordCount( $result );
          for ( $j = 0; $j < $nChunks; $j++ )
          {
            $row = $this->itsMODx->fetchRow( $result );
            $this->itsMODx->chunkCache[ $row['name'] ] = $row['snippet'];
            
            $find[] =
              '/\{\{' . preg_quote( $row['name'], '/' ) . '\}\}/'
              . $this->itsEncodingModifier;
            $replace[]= $this->PregQuoteReplacement( $row['snippet'] );
          }
        }
        if ( count( $find ) == 0 )
        {
          return FALSE;
        }
        $content = preg_replace( $find, $replace, $content, -1, $nReplacements );
        if ( $nReplacements == 0 )
        {
          return FALSE;
        }
        return TRUE;
      }
      return FALSE;
    }

//    private function MergeDocumentMETATags( $template )
//    {
//      // THis does the same as the equivalent MODx method except
//      // - It returns TRUE if the document has changed.
//      // - It assumes that th
//      if ($this->documentObject['haskeywords'] == 1)
//      {
//        // insert keywords
//        $keywords = $this->getKeywords();
//        if (is_array($keywords) && count($keywords) > 0)
//        {
//          $keywords = implode(", ", $keywords);
//          $metas= "\t<meta name=\"keywords\" content=\"$keywords\" />\n";
//        }
//
//        // Don't process when cached
//        $this->documentObject['haskeywords'] = '0';
//      }
//      if ($this->documentObject['hasmetatags'] == 1)
//      {
//        // insert meta tags
//        $tags= $this->getMETATags();
//        foreach ($tags as $n => $col)
//        {
//          $tag= strtolower($col['tag']);
//          $tagvalue= $col['tagvalue'];
//          $tagstyle= $col['http_equiv'] ? 'http-equiv' : 'name';
//          $metas .= "\t<meta $tagstyle=\"$tag\" content=\"$tagvalue\" />\n";
//        }
//
//        // Don't process when cached
//        $this->documentObject['hasmetatags'] = '0';
//      }
//    	if ($metas)
//      {
//        $template = preg_replace("/(<head>)/i", "\\1\n\t" . trim($metas), $template);
//      }
//      return $template;
//    }

    private function MergeDocumentContent( &$content )
    {
      // Returns true if the content has been changed.
      //
      // This is similar to the MODx function, except
      // - it doesn't delete dvs/tvs that have not been recognised.
      
      $find= array();
      $replace= array();
      $nMatches = preg_match_all(
        '/\[\*(#?)(.+?)\*\]/'
          . $this->itsEncodingModifier
        , $content
        , $matches
        );
      $basepath = $this->itsMODx->config['base_path'] . 'manager/includes';
      for ( $i= 0; $i < $nMatches; $i++ )
      {
        $key = $matches[2][$i];
        if ( ! is_array( $this->itsMODx->documentObject ) || ! array_key_exists( $key, $this->itsMODx->documentObject ) )
        {
          continue;
        }
        $value = $this->itsMODx->documentObject[ $key ];
        if ( is_array( $value ) )
        {
          include_once $basepath . '/tmplvars.format.inc.php';
          include_once $basepath . '/tmplvars.commands.inc.php';
          $w = '100%';
          $h = '300';
          $value = getTVDisplayFormat(
            $value[0]
            , $value[1]
            , $value[2]
            , $value[3]
            , $value[4]
            );
        }
        $find[] = '/' . preg_quote( $matches[0][$i], '/') . '/'
          . $this->itsEncodingModifier;
        $replace[] = $this->PregQuoteReplacement( $value );
      }
      if ( count( $find ) == 0 )
      {
        return FALSE;
      }
      $content = preg_replace( $find, $replace, $content, -1, $nReplacements );
      if ( $nReplacements == 0 )
      {
        return FALSE;
      }
      return TRUE;
    }
    
    private function MergeOtherDocumentContent( &$content )
    {
      // Parses (yams_data:{docId:}tv{:phx}) placeholders.
      // Returns true if the content has been changed.

      // First search out all the placeholders...
      // Place then in an array which has the following structure...
      // $cache = array(
      //  docId1 => array(
      //    'tv1' => array(
      //      'match1' => 'phx1'
      //       , 'match2' => 'phx2'
      //       , ...
      //      )
      //    'tv2' => array(
      //      'match1' => 'phx1'
      //       , 'match2' => 'phx2'
      //       , ...
      //      )
      //    , ...
      //    )
      // );

      $contentChanged = FALSE;

      $info = array();
      $nMatches = preg_match_all(
        '/\(\(yams_data:(([0-9]{0,13}):)?([^:]+)((:[.*])?)\)\)/U'
          . $this->itsEncodingModifier
        , $content
        , $matches
        );
      // Loop over the placeholders and
      $basepath = $this->itsMODx->config['base_path'] . 'manager/includes';
      for ( $i = 0; $i < $nMatches; $i++ )
      {
        $docId = $matches[ 2 ][ $i ];
        if ( $docId == '' )
        {
          $docId = $this->itsMODx->documentIdentifier;
        }
        if ( ! array_key_exists( $docId, $info ) )
        {
          $info[ $docId ] = array();
        }
        $docCache = &$info[ $docId ];
        $tv = $matches[ 3 ][ $i ];
        $phx = $matches[ 4 ][ $i ];
        $match = '/' . preg_quote( $matches[ 0 ][ $i ], '/') . '/'
          . $this->itsEncodingModifier;
        if ( ! array_key_exists( $tv, $docCache ) )
        {
          $docCache[ $tv ] = array();
        }
        $docCache[ $tv ][ $match ] = $phx;
      }
      // Now loop over the cache array and write an SQL statement
      // that will grab the information from the database
      // a maximum of YAMS_DOC_LIMIT docs at a time
      $docIds = array_keys( $info );
      $nDocs = count( $docIds );
      $sc   = $this->itsMODx->getFullTableName('site_content');
      $st   = $this->itsMODx->getFullTableName('site_tmplvars');
      $stt  = $this->itsMODx->getFullTableName('site_tmplvar_templates');
      $stc  = $this->itsMODx->getFullTableName('site_tmplvar_contentvalues');
      for ( $i = 0; $i < $nDocs; $i = $i + YAMS_DOC_LIMIT )
      {
        $sqlArray = array();
        $jMax = min( $nDocs, $i + YAMS_DOC_LIMIT );
        for ( $j = $i; $j < $jMax; $j++ )
        {
          $docId = $docIds[ $j ];
          $docCache = &$info[ $docId ];
          $inArray = array_keys( $docCache );
          foreach ( $inArray as &$tvName )
          {
            $tvName = '\'' . $this->itsMODx->db->escape( $tvName ) . '\'';
          }
          $inList = implode( ',', $inArray );
          $sqlArray[] =
            '(SELECT'
              . ' sc.id AS docid'
              . ', st.name AS name'
              . ', IF(stc.value != \'\',stc.value,st.default_text) AS value'
              . ', st.display AS display'
              . ', st.display_params AS display_params'
              . ', st.type AS type'
            . ' FROM '
              . $st . ' st'
            . ' LEFT JOIN ' . $stc . ' stc'
              . ' ON st.id = stc.tmplvarid'
              . ' AND stc.contentid = ' . $docId
            . ' INNER JOIN ' . $stt . ' stt'
              . ' ON stt.tmplvarid = st.id'
            . ' INNER JOIN ' . $sc . ' sc'
              . ' ON sc.id = ' . $docId
            . ' WHERE'
              . ' st.name IN (' . $inList . ')'
              . ' AND sc.deleted = 0'
            . ')';
        }
        if ( count( $sqlArray ) == 0 )
        {
          continue;
        }
        $sql = implode( ' UNION ', $sqlArray ) . ';';
        // Grab the data from the database...
        $result = $this->itsMODx->db->query( $sql );
        $count = $this->itsMODx->recordCount( $result );
        // Set up find and replace arrays...
        $find = array();
        $replace = array();
        for ( $j = 0; $j < $count; $j++ )
        {
          $row = $this->itsMODx->fetchRow($result);
          $docId = &$row[ 'docid' ];
          $tvName = &$row[ 'name' ];
          $tvValue = &$row[ 'value' ];
          $tvDisplay = &$row[ 'display' ];
          $tvDisplayParams = &$row[ 'display_params'];
          $tvType = &$row[ 'type'];
          $matches = &$info[ $docId ][ $tvName ];
          foreach ( $matches as $match => $phx )
          {
            include_once $basepath . '/tmplvars.format.inc.php';
            include_once $basepath . '/tmplvars.commands.inc.php';
            $w = '100%';
            $h = '300';
            $value = getTVDisplayFormat(
              $tvName
              , $tvValue
              , $tvDisplay
              , $tvDisplayParams
              , $tvType
              );
            $find[] = $match;
            $replace[] = $this->PregQuoteReplacement( $value );
            // TO DO: PHx stuff...
          }          
        }
        if ( count( $find ) == 0 )
        {
          continue;
        }
        $content = preg_replace( $find, $replace, $content, -1, $nReplacements );
        if ( $nReplacements > 0 )
        {
          $contentChanged = TRUE;
        }
      }
      return $contentChanged;
    }
    
//    private function ParseChunkCallback( $matches )
//    {
//      $chunkName = $matches[1];
//      if ( ! array_key_exists( $chunkName, $this->itsMODx->chunkCache ) )
//      {
//        // do nothing
//        return $matches[0];
//      }
//      $content = $this->itsMODx->getChunk( $chunkName );
//      //recurse
//      // NOTE: There is no protection from infinite loops here.
//      // ie; chunk containing a chunk with the same name
//      $content
//        = preg_replace_callback(
//          '/\{\{([^\n\}]+)\}\}/U'
//          . $this->itsEncodingModifier
//          , array($this, 'ParseChunkCallback')
//          , $content
//          , -1
//        );
//      $this->itsCallbackCounter++;
//      return $content;
//    }

    private function SelectLangCallback( $matches )
    {
      $mode = $matches[1];
      switch ( $mode )
      {
        case '+':
          $langId = $this->itsSelectLangId;
          break;
        case '':
        default:
          $langId = $this->itsParseLangId;
      }
      // Split up the yams block by language
      $translations
        = preg_split(
          '/\(lang:'
          . preg_quote( $matches[2], '/' )
          . ':(.*)\)/U' . $this->itsEncodingModifier
          , $matches[3]
          , -1
          , PREG_SPLIT_DELIM_CAPTURE
        );

      // Loop over the translations and return the
      // one corresponding to the current language if it exists
      $nTranslations = count( $translations );
      for ( $i = 1; $i < $nTranslations; $i = $i + 2 )
      {
        if ( $translations[ $i ] == $langId )
        {
          // Select the correct language from multilanguage sections
          return $translations[ $i + 1 ];
        }
      }
      // No translation was found for the current language
      // Return nothing...
      return '';
//      return '<!--SelectLangCallback:' . $langId . '-->';
//      // Return the default language
//      // ** In future, might modify this to allow some other
//      // text to be supplied, such as *please translate me*
//      return $translations[ 2 ];
    }

    private function MultiLangCallback( $matches )
    {
      return $this->MultiLangExpand(
          '[' . $matches[1]
          , $matches[3]
          , NULL
          , ''
          , $matches[2]
          , $matches[4]
        );
    }

    private function YamsInCallback( $matches )
    {
      $content = $matches[4];
      $langId = $matches[3];
      if ( ! $this->IsActiveLangId( $langId ) )
      {
        return '';
      }
      if ( $content == '' )
      {
        return '';
      }

      $oldParseLangId = $this->itsParseLangId;
      $this->itsParseLangId = $langId;

      // Don't update itsSelectLangId here.

      // save the lang id
      // $callbackLangId = $this->itsCallbackLangId;
      $success = $this->PostParse(
        $this->itsCallbackIsMultilingualDocument
        , $content
        );

      // restore the in lang id
      $this->itsParseLangId = $oldParseLangId;

      if ( ! $success )
      {
        return '';
      }
      return $content;
    }

    private function StoreYamsInCallback( $matches )
    {
      $content = $matches[4];
      $langId = $matches[3];
      $yamsCounter = $matches[1];
      if ( ! $this->IsActiveLangId( $langId ) )
      {
        return '';
      }
      if ( $content == '' )
      {
        return '';
      }

      $oldParseLangId = $this->itsParseLangId;
      $this->itsParseLangId = $langId;

      // Don't update itsSelectLangId here.

      $output = '';
      // recurse...
      $content
        = preg_replace_callback(
          '/'
            . '(?>\(yams-in:([0-9]{1,10})(:([^\)]+))?\))'
            . '(.*?)'
            . '\(\/yams-in:\1\)'
            . '/s'
            . $this->itsEncodingModifier
          , array($this, 'StoreYamsInCallback')
          , $content
          , -1
          , $count
        );
      if ( $content == '' )
      {
        return '';
      }
      $newYamsCounter = $this->GetYamsCounter();
      // store the content...
      $this->itsYamsInContent[ $newYamsCounter ] = array(
        'langId' => $langId
        , 'content' => $content
        );

      $this->itsParseLangId = $oldParseLangId;
      // replace the content by a placeholder
      return '(yams-out:' . $newYamsCounter . '/)';
    }

    private function StoreYamsRepeatCallback( $matches )
    {
      $langsText = $matches[3];
      if ( $langsText == '' )
      {
        $langIds = $this->itsActiveLangIds;
      }
      else
      {
        $langTextIds = explode(',', $langsText);
        $langIds = array();
        foreach ( $langTextIds as $langId )
        {
          if ( $this->IsActiveLangId( $langId ) )
          {
            $langIds[] = $langId;
          }
        }
      }

      $yamsCounter = $matches[1];

      // Check if there is a default block
      $templates = preg_split(
        '/\(current:' . preg_quote( $yamsCounter, '/') . '\)/'
          . $this->itsEncodingModifier
        , $matches[4]
        , 2
      );
      // unset( $content );

      // recurse...
      $content
        = preg_replace_callback(
          '/'
            . '(?>\(yams-repeat:([0-9]{1,10})(:([^\)]+))?\))'
            . '(.*?)'
            // . '(\((current):\1\)(.*))?'
            . '\(\/yams-repeat:\1\)'
            . '/s'
            . $this->itsEncodingModifier
          , array($this, 'StoreYamsRepeatCallback')
          , $templates[0]
          , -1
          , $count
        );
      
      // If default text was specified..
      if ( count( $templates ) == 2 )
      {
        // recurse...
        $currentLangContent
          = preg_replace_callback(
            '/'
              . '(?>\(yams-repeat:([0-9]{1,10})(:([^\)]+))?\))'
              . '(.*?)'
              // . '(\((current):\1\)(.*))?'
              . '\(\/yams-repeat:\1\)'
              . '/s'
              . $this->itsEncodingModifier
            , array($this, 'StoreYamsRepeatCallback')
            , $templates[1]
            , -1
            , $count
          );
      }
      else
      {
        $currentLangContent = NULL;
      }

      if ( $content == '' && $currentLangContent == '' )
      {
        return '';
      }

      $newYamsCounter = $this->GetYamsCounter();
      // store the content...
      $this->itsYamsRepeatContent[ $newYamsCounter ] = array(
        'langIds' => $langIds
        , 'content' => $content
        , 'currentLangContent' => $currentLangContent
        );
      // replace the content by a placeholder
      return '(yams-repeat-out:' . $newYamsCounter . '/)';
    }

    private function RestoreYamsInCallback( $matches )
    {
      $yamsCounter = $matches[1];
      if ( ! array_key_exists( $yamsCounter, $this->itsYamsInContent ) )
      {
        return '';
      }
      $isMultilingualDocument = $this->itsCallbackIsMultilingualDocument;
      $content = $this->itsYamsInContent[ $yamsCounter ]['content'];
      $langId = $this->itsYamsInContent[ $yamsCounter ]['langId'];

      // It *might* be safe to unset this here
      // unset( $this->itsYamsInContent[ $yamsCounter ] );

      // Don't update itsSelectLangId here.

      $oldParseLangId = $this->itsParseLangId;
      $this->itsParseLangId = $langId;
      $success = $this->PostParse(
          $this->itsCallbackIsMultilingualDocument
          , $content
        );
      $this->itsCallbackIsMultilingualDocument = $isMultilingualDocument;
      if ( ! $success || $content == '' )
      {
        $this->itsParseLangId = $oldParseLangId;
        return '';
      }

      // recurse...
      $content
        = preg_replace_callback(
          '/'
            . '\(yams-out:([0-9]{1,10})\/\)'
            . '/U'
            . $this->itsEncodingModifier
          , array($this,'RestoreYamsInCallback')
          , $content
          , -1
          , $count
        );

      $this->itsParseLangId = $oldParseLangId;

      if ( $content == '' )
      {
        return '';
      }
      return '(yams-in:' . $yamsCounter . ':' . $langId . ')'
        . $content
        . '(/yams-in:' . $yamsCounter . ')';
    }

    private function RestoreYamsRepeatCallback( $matches )
    {
      $yamsCounter = $matches[1];
      if ( ! array_key_exists( $yamsCounter, $this->itsYamsRepeatContent ) )
      {
        return '';
      }
      $isMultilingualDocument = $this->itsCallbackIsMultilingualDocument;
      $langIds = $this->itsYamsRepeatContent[ $yamsCounter ]['langIds'];
      $content = $this->itsYamsRepeatContent[ $yamsCounter ]['content'];
      $currentLangContent = $this->itsYamsRepeatContent[ $yamsCounter ]['currentLangContent'];

      // It *might* be safe to unset this here
      // unset( $this->itsYamsRepeatContent[ $yamsCounter ] );
      
      if ( is_null( $currentLangContent ) )
      {
        $currentLangContent = $content;
      }
      $output = '';
      foreach ( $langIds as $langId )
      {
        // repeat the content for each language
        $yamsInCounter = $this->GetYamsCounter();
        $output .=
          '(yams-out:' . $yamsInCounter . '/)';
        // add to the yams in array...
        if ( $langId == $this->itsSelectLangId )
        {
          $this->itsYamsInContent[ $yamsInCounter ] = array(
            'langId' => $langId
            , 'content' => $currentLangContent
            );
        }
        else
        {
          $this->itsYamsInContent[ $yamsInCounter ] = array(
            'langId' => $langId
            , 'content' => $content
            );
        }
        $oldParseLangId = $this->itsParseLangId;
        $this->itsParseLangId = $langId;
        // recurse...
        $this->itsYamsInContent[ $yamsInCounter ]['content']
          = preg_replace_callback(
            '/'
              . '\(yams-repeat-out:([0-9]{1,10})\/\)'
              . '/U'
              . $this->itsEncodingModifier
            , array($this,'RestoreYamsRepeatCallback')
            , $this->itsYamsInContent[ $yamsInCounter ]['content']
            , -1
            , $count
          );

        $this->itsParseLangId = $oldParseLangId;
      }
      return $output;
    }

    private function MultiLangYamsCallbackMulti( $matches )
    {
      if ( $matches[2] == 'name_in_' )
      {
        $yamsTag = $matches[2];
        $inLangId = $matches[3];
      }
      else
      {
        $yamsTag = $matches[1];
        $inLangId = '';
      }
      $mode = $matches[4];
      switch ( $mode )
      {
        case '+':
          $langId = $this->itsSelectLangId;
          break;
        case '':
        default:
          $langId = $this->itsParseLangId;
      }
      $docId = $this->itsCallbackDocId;
      $isMultilingualDocument = $this->itsCallbackIsMultilingualDocument;
      if ( array_key_exists(5, $matches) && $matches[5] != '' )
      {
        $docId = $matches[6];
        $isMultilingualDocument = $this->IsMultilingualDocument( $docId );
      }
      switch ( $yamsTag )
      {
      case 'id':
//        if ( $isMultilingualDocument )
//        {
        $expandArray =
          array_combine(
            $this->itsActiveLangIds
            , $this->itsActiveLangIds
            );
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
//        }
//        else
//        {
//          $output = $langId;
//        }
        break;
      case 'tag':
//        if ( $isMultilingualDocument )
//        {
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetPrimaryLangTag( $langId );
        }
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
//        }
//        else
//        {
//          $output = $this->GetPrimaryLangTag( $langId );
//        }
        break;
      case 'name':
//        if ( $isMultilingualDocument )
//        {
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetLangName( $langId );
        }
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
//        }
//        else
//        {
//          $output = $this->GetLangName( $langId );
//        }
        break;
      case 'name_in_':
//        if ( $isMultilingualDocument )
//        {
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetLangName( $inLangId, $langId );
        }
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
//        }
//        else
//        {
//          $output = $this->GetLangName( $inLangId, $langId );
//        }
        break;
      case 'mname':
//        if ( $isMultilingualDocument )
//        {
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetMODxLangName( $langId );
        }
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
//        }
//        else
//        {
//          $output = $this->GetMODxLangName( $langId );
//        }
        break;
      case 'root':
        if ( $isMultilingualDocument )
        {
          $expandArray = array();
          foreach ( $this->itsActiveLangIds as $langId )
          {
            $expandArray[ $langId ]
              = $this->GetActiveRootName( $langId );
          }
          $output = $this->Expand(
            'text'
            , $expandArray
            , NULL
            , $mode
            );
        }
        else
        {
          $output = '';
        }
        break;
      case 'root/':
        if ( $isMultilingualDocument )
        {
          $expandArray = array();
          foreach ( $this->itsActiveLangIds as $langId )
          {
            $expandArray[ $langId ]
              = $this->GetActiveRootName( $langId );
            if ( $expandArray[ $langId ] != '' )
            {
              $expandArray[ $langId ] .= '/';
            }
          }
          $output = $this->Expand(
            'text'
            , $expandArray
            , NULL
            , $mode
            );
        }
        else
        {
          $output = '';
        }
        break;
      case '/root':
        if ( $isMultilingualDocument )
        {
          $expandArray = array();
          foreach ( $this->itsActiveLangIds as $langId )
          {
            $expandArray[ $langId ]
              = $this->GetActiveRootName( $langId );
            if ( $expandArray[ $langId ] != '' )
            {
              $expandArray[ $langId ] = '/' . $expandArray[ $langId ];
            }
          }
          $output = $this->Expand(
            'text'
            , $expandArray
            , NULL
            , $mode
            );
        }
        else
        {
          $output = '';
        }
        break;
      case 'site':
        if ( $isMultilingualDocument )
        {
          $expandArray = array();
          foreach ( $this->itsActiveLangIds as $langId )
          {
            $expandArray[ $langId ]
              = $this->GetSiteURL( $langId );
          }
          $output = $this->Expand(
            'text'
            , $expandArray
            , NULL
            , $mode
            );
        }
        else
        {
          $output = $this->GetSiteURL( NULL );
        }
        break;
      case 'server':
        if ( $isMultilingualDocument )
        {
          $expandArray = array();
          foreach ( $this->itsActiveLangIds as $langId )
          {
            $expandArray[ $langId ]
              = $this->ConstructURL(
                  $langId
                  , NULL
                  // , FALSE
                  , FALSE
                  , TRUE
                  , FALSE
                  , FALSE
                  , FALSE );
          }
          $output = $this->Expand(
            'text'
            , $expandArray
            , NULL
            , $mode
            );
        }
        else
        {
          $output = $this->ConstructURL(
              NULL
              , NULL
              // , FALSE
              , FALSE
              , TRUE
              , FALSE
              , FALSE
              , FALSE
            );
        }
        break;
      case 'dir':
//        if ( $isMultilingualDocument )
//        {
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetLangDir( $langId );
        }
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
//        }
//        else
//        {
//          $output = $this->GetLangDir( $langId );
//        }
        break;
      case 'align':
//        if ( $isMultilingualDocument )
//        {
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetLangAlign( $langId );
        }
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
//        }
//        else
//        {
//          $output = $this->GetLangAlign( $langId );
//        }
        break;
      case 'doc':
        if ( $isMultilingualDocument )
        {
          $expandArray = array();
          foreach ( $this->itsActiveLangIds as $langId )
          {
            $expandArray[ $langId ]
              = $this->GetDocURL( $docId, $langId );
          }
          $output = $this->Expand(
            'text'
            , $expandArray
            , NULL
            , $mode
          );
        }
        else
        {
          $output = $this->GetDocURL( $docId, NULL );
        }
        // error_log( 'doc: ' . $output );
        break;
      case 'docr':
        // Determine if the current document is a weblink
        $output = $this->GetDocResolvedURL(
          $docId
          , $mode
          );
        break;
      case 'confirm':
        $output = $this->itsLangQueryParam;
        break;
      case 'change':
        $output = $this->itsChangeLangQueryParam;
        break;
      case 'choose':
//        if ( $isMultilingualDocument )
//        {
        $expandArray = $this->itsChooseLangText;
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
//        }
//        else
//        {
//          $output = $this->GetChooseLangText( $langId );
//        }
        break;
      default:
        $output = '';
      }

      return $output;

    }

    private function MultiLangEasyLingualCallbackMulti( $matches )
    {
      $yamsTag = $matches[1];
      $mode = $matches[2];
      switch ( $mode )
      {
        case '+':
          $langId = $this->itsSelectLangId;
          break;
        case '':
        default:
          $langId = $this->itsParseLangId;
      }
      switch ( $yamsTag )
      {
      case 'lang':
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetPrimaryLangTag( $langId );
        }
        $expandArray[ $this->itsDefaultLangId ] = '';
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
        break;
      case 'LANG':
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetPrimaryLangTag( $langId );
        }
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
        break;
      case 'language':
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetMODxLangName( $langId );
        }
        $expandArray[ $this->itsDefaultLangId ] = '';
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
        break;
      case 'LANGUAGE':
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetMODxLangName( $langId );
        }
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
        break;
      case 'dir':
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetLangDir( $langId );
        }
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
        break;
      case 'align':
        $expandArray = array();
        foreach ( $this->itsActiveLangIds as $langId )
        {
          $expandArray[ $langId ]
            = $this->GetLangAlign( $langId );
        }
        $output = $this->Expand(
          'text'
          , $expandArray
          , NULL
          , $mode
          );
        break;
      default:
        $output = '';
      }

      return $output;

    }

//    private function MultiLangEasyLingualCallbackMono( $matches )
//    {
//      $yamsTag = $matches[1];
//      $mode = $matches[2];
//      switch ( $mode )
//      {
//        case '+':
//          $langId = $this->itsSelectLangId;
//          break;
//        case '':
//        default:
//          $langId = $this->itsParseLangId;
//      }
//      switch ( $yamsTag )
//      {
//      case 'lang':
//        $output = '';
//        break;
//      case 'LANG':
//        $output = $this->GetPrimaryLangTag( $langId );
//        break;
//      case 'language':
//        $output = '';
//        break;
//      case 'LANGUAGE':
//        $output = $this->GetLangName( $langId );
//        break;
//      case 'dir':
//        $output = $this->GetLangDir( NULL );
//        break;
//      case 'align':
//        $output = $this->GetLangAlign( NULL );
//        break;
//      default:
//        $output = '';
//      }
//      return $output;
//    }
//
    private function ExpandRepeatTemplates(
      $beforetpl = NULL
      , $repeattpl = NULL
      , $currenttpl = NULL
      , $aftertpl = NULL
    )
    {
      if ( !is_string( $beforetpl ) )
      {
        $beforetpl = '';
      }
      else
      {
        $success = $this->GetTemplate( $beforetpl );
        if ( $success === FALSE )
        {
          $beforetpl = '';
        }
        else
        {
          $beforetpl = $success;
        }
      }

      if ( !is_string( $repeattpl ) )
      {
        $repeattpl = '';
      }
      else
      {
        $success = $this->GetTemplate( $repeattpl );
        if ( $success === FALSE )
        {
          $repeattpl = '';
        }
        else
        {
          $repeattpl = $success;
        }
      }

      if ( is_string( $currenttpl ) )
      {
        $success = $this->GetTemplate( $currenttpl );
        if ( $success === FALSE )
        {
          $currenttpl = '';
        }
        else
        {
          $currenttpl = $success;
        }
      }

      if ( !is_string( $aftertpl ) )
      {
        $aftertpl = '';
      }
      else
      {
        $success = $this->GetTemplate( $aftertpl );
        if ( $success === FALSE )
        {
          $aftertpl = '';
        }
        else
        {
          $aftertpl = $success;
        }
      }

      $yamsCounter = $this->GetYamsCounter();
      if ( is_string( $currenttpl ) )
      {
        $currentBlock =
          '(current:' . $yamsCounter . ')'
          . $currenttpl;
      }
      else
      {
        $currentBlock = '';
      }

      return $beforetpl
        . '(yams-repeat:' . $yamsCounter . ')'
        . $repeattpl
        . $currentBlock
        . '(/yams-repeat:' . $yamsCounter . ')'
        . $aftertpl;
    }

    private function GetTemplate( $source )
    {
      // based on a version in Wayfinder 2.0... which was...
      // based on version by Doze at http://modxcms.com/forums/index.php/topic,5344.msg41096.html#msg41096
      if ( array_key_exists( $source, $this->itsMODx->chunkCache ) )
      {
        return $this->itsMODx->getChunk( $source );
      }
//      $template = $this->itsMODx->getChunk( $source );
//      if (  $template != '' )
//      {
//        return $template;
//      }
      // $atCode = substr( $source, 0, 6 );
      // if ( $atCode == '@FILE:' )
      if ( preg_match(
          '/^@FILE:/'
            . $this->itsEncodingModifier
          , $source
        ) == 1 )
      {
        $filename = substr( $source, 6 );
        $template = $this->GetFileContents( $filename );
        return $template;
      }
//      if ( $atCode == '@CODE:' )
      if ( preg_match(
          '/^@CODE:/'
            . $this->itsEncodingModifier
          , $source
        ) == 1 )
      {
        $template = substr( $source, 6 );
        return $template;
      }
      return FALSE;
    }

    private function GetFileContents( $filename )
    {
      if ( ! function_exists( 'file_get_contents' ) )
      {
        $fhandle = fopen( $filename, 'r' );
        $fcontents = fread( $fhandle, filesize( $filename ) );
        fclose( $fhandle );
      }
      else
      {
        $fcontents = file_get_contents( $filename );
      }
      return $fcontents;
    }

    private function UrlEncode( $string, $encode = TRUE )
    {
      // To properly encode an url
      // 1) Encode string in UTF-8
      // 2) rawurlencode
      //
      // $_GET is automatically rawurldecoded... but it is left
      // in UTF-8 as far as I know, since PHP doesn't know what else
      // to do with it.

      $modxCharset = $this->itsMODx->config['modx_charset'];

      if ( $modxCharset != 'UTF-8' )
      {
        $newString = iconv( $modxCharset, 'UTF-8', $string );
        if ( ! ( $newString === FALSE ) )
        {
          $string = $newString;          
        }
      }

      if ( $encode )
      {
        return rawurlencode( $string );
      }
      
      return $string;
      
    }

    private function UrlDecode( $string, $decode = FALSE )
    {
      // $_GET is automatically rawurldecoded... but it is left
      // in UTF-8 as far as I know, since PHP doesn't know what else
      // to do with it.
      //
      // So, don't need to rawurldecode it,
      // but do need to convert it to the correct character encoding.

      $modxCharset = $this->itsMODx->config['modx_charset'];

      if ( $decode )
      {
        $string = rawurldecode( $string );
      }

      if ( $modxCharset != 'UTF-8' )
      {
        $newString = iconv( 'UTF-8', $modxCharset, $string );
        if ( ! ( $newString === FALSE ) )
        {
          $string = $newString;
        }
      }
      return $string;

    }

//    private function MultiLangYamsCallbackDocId( $matches )
//    {
//      $docId = $matches[5];
//
//      $isMultilingualDocument = $this->IsMultilingualDocument( $docId );
//      $this->itsCallbackDocId = $docId;
//      if ( $isMultilingualDocument )
//      {
//        return $this->MultiLangYamsCallbackMulti( $matches );
//      }
//      else
//      {
//        return $this->MultiLangYamsCallbackMono( $matches );
//      }
//    }

//    private function UrlEncode( $string )
//    {
//      if ( $this->itsEncodingModifier == 'u' )
//      {
//        return urlencode( utf8_encode( $string ) );
//      }
//      else
//      {
//        return urlencode( $string );
//      }
//    }

    private function Initialise()
    {
      global $modx;

      // This is close to the maximum size allowed in the content field
      ini_set('pcre.backtrack_limit', '16000000');

      $this->itsOutputQuerySeparator = ini_get( 'arg_separator.output' );
      if ( is_null( $this->itsOutputQuerySeparator ) )
      {
        $this->itsOutputQuerySeparator = '&amp;';
      }
      $this->itsInputQuerySeparator = ini_get( 'arg_separator.input' );
      if ( is_null( $this->itsInputQuerySeparator ) )
      {
        $this->itsInputQuerySeparator = '&';
      }


      $this->itsMODx = &$modx;

      $this->itsDocVarNames = array_keys( $this->itsDocVarTypes );

      @include( dirname( __FILE__ ) . '/../yams.config.inc.php');

      // Check if UTF-8 is being used
      // (Assume the encoding of the web page output
      // is the same as the encoding of the manager)
      switch ( $this->itsEncodingModifierMode )
      {
      case '':
      case 'u':
        $this->itsEncodingModifier = $this->itsEncodingModiferMode;
        break;
      default:
        $this->itsEncodingModifier = '';
        if ( $this->itsMODx->config['modx_charset'] == 'UTF-8')
        {
          $this->itsEncodingModifier = 'u';
        }
      }

      $this->UpdateLanguageDependentServerNamesMode();
      $this->UpdateLanguageDependentRootNamesMode();
      $this->UpdateLanguageQueryParamMode();
      $this->UpdateUniqueMultilingualAliasMode();
      $this->UpdateMonolingualDocIds();
      $this->CacheDocumentAliasInfo();

      // Set the current language
      $this->itsCurrentLangId = $this->DetermineCurrentLangId();
      // $this->itsCurrentLangId = $this->itsDefaultLangId;

    }

    // --
    // -- Default Attributes
    // -- These can be overriden using the yams.config.inc.php file
    // --
    // A list of active lang ids
    private $itsActiveLangIds = array(
      'en'
      );
    // A list of inactive lang ids
    private $itsInactiveLangIds = array(
      'fr'
      , 'ja'
      , 'de'
      , 'ru'
      );
    // Specifies the language direction, ltr or rtl
    private $itsIsLTR = array(
      'en' => TRUE
      , 'fr' => TRUE
      , 'ja' => TRUE
      , 'de' => TRUE
      , 'ru' => TRUE
      );
    // The default language id
    private $itsDefaultLangId = 'en';
    // The name of the root folder
    // eg: http://mysite.com/rootfolder
    // use empty string for no folder
    private $itsRootName = array(
      'en' => 'en'
      , 'fr' => 'fr'
      , 'ja' => 'ja'
      , 'de' => 'de'
      , 'ru' => 'ru'
      );
    // Use to define the server name for monolingual webpages
    // Use empty string for default server name ( as provided by [(site_url)] )
    private $itsMonoServerName = '';
    // Use to set the server name by language
    // No protocol and no trailing slash.
    // eg: www.mylanguage.mysite.com
    // use empty string for the default server name
    private $itsMultiServerName = array(
      'en' => ''
      , 'fr' => ''
      , 'ja' => ''
      , 'de' => ''
      , 'ru' => ''
      );
    // The name of the language in the default lang
    // and any other languges.
    private $itsLangNames = array(
      'en' => array(
          'en' => 'English'
          , 'fr' => '(French)'
          , 'ja' => '(Japanese)'
          , 'de' => '(German)'
          , 'ru' => '(Russian)'
          )
      , 'fr' => array(
          'en' => '(Anglais)'
          , 'fr' => 'Français'
          , 'ja' => '(Japonais)'
          , 'de' => '(Allemand)'
          , 'ru' => '(Russe)'
          )
      , 'ja' => array(
          'en' => '（英語）'
          , 'fr' => '（フランス語）'
          , 'ja' => '日本語'
          , 'de' => '（ドイツ語）'
          , 'ru' => '（ロシア語）'
        )
      , 'de' => array(
          'en' => '(Englisch)'
          , 'fr' => '(Französisch)'
          , 'ja' => '(Japanisch)'
          , 'de' => 'Deutsch'
          , 'ru' => '(Russisch)'
        )
      , 'ru' => array(
          'en' => '(Английский)'
          , 'fr' => '(Французский)'
          , 'ja' => '(Японский)'
          , 'de' => '(Немецкий)'
          , 'ru' => 'Русский'
        )
      );
    // The 'Choose language' text in the given language
    private $itsChooseLangText = array(
      'en' => 'Select language'
      , 'fr' => 'Choisir une langue'
      , 'ja' => '言語選択'
      , 'de' => 'Waehle Sprache'
      , 'ru' => 'Выберите язык'
      );
    // The languages that should be directed to this language root.
    // These should be in priority order
    // The tag is in the format provided by the HTTP Accept-Language header:
    // xx, or xx-yy, where
    // xx: is a two letter language abbreviation
    //     http://www.loc.gov/standards/iso639-2/php/code_list.php
    // yy: is a two letter country code
    //     http://www.iso.org/country_codes/iso_3166_code_lists/english_country_names_and_code_elements.htm
    // xx on its own matches an xx Accept-Language header
    // with any country code
    // At least one language tag must be specified for each active language.
    // eg: array( 'en-gb', 'en-us' )
    // or: array( 'fr-ca', 'fr-be' )
    private $itsLangTags = array(
      'en' => array( 'en' )
      , 'fr' => array( 'fr' )
      , 'ja' => array( 'ja' )
      , 'de' => array( 'de' )
      , 'ru' => array( 'ru' )
      );
    // The modx language name, or the empty string if none set.
    private $itsMODxLangName = array(
      'en' => 'english'
      , 'fr' => 'francais-utf8'
      , 'ja' => 'japanese-utf8'
      , 'de' => 'german'
      , 'ru' => 'russian-UTF8'
      );
    // The encoding modifier selection mode.
    // 'manager' means use the manager setting
    // 'u' means the webpage content is in UTF-8
    // '' means that it is not UTF-8 encoded
    private $itsEncodingModifierMode = 'manager';
    // a comma separated list of active template ids
    // if the default activity is none
    private $itsActiveTemplates = array();
    // Whether or not to sync template variables
    private $itsManageTVs = TRUE;
    // The yams lang query parameter name
    private $itsLangQueryParam = 'yams_lang';
    // The yams change lang query/post parameter name
    private $itsChangeLangQueryParam = 'yams_new_lang';

    // Turn on/off redirection from existing pages to multilingual pages
    // You can set to FALSE if you are developing a site from scratch
    // - although leaving as TRUE does not harm in this instance
    // Set to TRUE if you are converting a website
    // that has already been made public
    // private $itsRedirectionMode = TRUE;

    // The redirection mode. Can be:
    // none: No redirection
    // default: Redirect to the equivalent default language page
    // browser: Redirect the a browser language if available,
    //          else the default language page.
    private $itsRedirectionMode = 'default';
    // The type of http redirection to perform when redirecting to the default
    // language
    private $itsHTTPStatus = 307;
    // The type of http redirection to perform when redirecting to a non-default
    // language
    private $itsHTTPStatusNotDefault = 303;
    // The type of http redirection to perform when responding to a request to change language
    // language
    private $itsHTTPStatusChangeLang = 303;
    // Whether or not to hide the original fields
    // For use with manager manager
    private $itsHideFields = FALSE;
    // Whether or not to place tvs for individual languages on separate tabs
    // For use with manager manager
    private $itsTabifyLangs = TRUE;
    // Whether or not to synchronise the document pagetitle with the default language pagetitle
    private $itsSynchronisePagetitle = FALSE;
    // Whether or not to use EasyLingual compatibility mode
    private $itsEasyLingualCompatibility = FALSE;
    // Whether or not to show the site_start document alias.
    private $itsShowSiteStartAlias = TRUE;
    // Whether or not to rewrite containers as folders.
    private $itsRewriteContainersAsFolders = FALSE;
    // If MODx is installed into a subdirectory then this param
    // can be used to specify the path to that directory.
    // (with a trailing slash and no leading slash)
    private $itsMODxSubdirectory = '';
    // The URL conversion mode
    // none: Don't do any automatic conversion of MODx URLs.
    // default: Convert MODx URLs surrounded by double quotes to (yams_doc:id) placeholders
    // resolve: Convert MODx URLs surrounded by double quotes to (yams_docr:id) placeholders
    // The default is doc, which replicates standard MODx behaviour, but
    // docr might be more useful.
    private $itsURLConversionMode = 'default';
    // Whether or not to use multilingual aliases
    private $itsUseMultilingualAliases = FALSE;
    // Whether multilingual aliases will be unique
    // If TRUE, the default when creating new aliases is langId-documentalias
    // If FALSE, the default when creating new aliases documentalias
    private $itsMultilingualAliasesAreUnique = FALSE;
//    // Whether or not to hide multilingual aliases when they have been disactivated
//    private $itsHideUnusedMultilingualAliaes = TRUE;
    // Whether or not to determine the document suffix based on mime type
    private $itsUseMimeDependentSuffixes = FALSE;
    // The mime suffix mapping
    private $itsMimeSuffixMap = array(
      'application/xhtml+xml' => '.xhtml'
      , 'application/javascript' => '.js'
      , 'text/javascript' => '.js'
      , 'application/rss+xml' => '.rss'
      , 'application/xml' => '.xml'
      , 'text/xml' => '.xml'
      , 'text/css' => '.css'
      , 'text/html' => '.html'
      , 'text/plain' => '.txt'
      );
    // A mapping from langIds to roles.
    // Says which roles have access to each language.
    // If an empty string is provided all roles have access
    // If no key is provided for a language all roles have access
    private $itsLangRolesAccessMap = array();
    private $itsUseStripAlias = TRUE;
    // An array of doc ids for which URLs of the form index.php?id= ... will be
    // accepted - even if friendly aliases are being used.
    private $itsAcceptMODxURLDocIds = array();

    // An array temporarily containing yams-in content
    // during the preparse stage.
    private $itsYamsInContent = array();
    // An array temporarily containing yams-repeat content
    // during the preparse stage.
    private $itsYamsRepeatContent = array();
    // A modx object instance.
    // Avoids having to use global all over the place
    private $itsMODx = NULL;
    // The current language id for the document
    private $itsCurrentLangId = 'en';
    // The current language id for the part of the document being parsed
    private $itsParseLangId = 'en';
    // The language id of the part of the select block currently being parsed.
    // This doesn't change when yams-in blocks are being parsed.
    private $itsSelectLangId = 'en';
    // The yams block counter
    private $itsYamsCounter = 0;
    // The encoding modifier
    private $itsEncodingModifier = 'u';
    // For sharing with callbacks
    // The parse language id
    // private $itsCallbackLangId = '';
    // For sharing with callbacks
    // The document id
    private $itsCallbackDocId = '';
//    // For sharing with callbacks
//    // A counter
//    private $itsCallbackCounter = 0;
    // For sharing with callbacks
    // Whether or not a document is multilingual
    private $itsCallbackIsMultilingualDocument = FALSE;
    // Whether or not to use language dependent server names
    private $itsUseLanguageDependentServerNames = FALSE;
    // Whether or not to use language dependent root names
    private $itsUseLanguageDependentRootNames = FALSE;
    // Whether or not a query param is required to identify the language
    private $itsUseLanguageQueryParam = FALSE;
    // Whether or not unique multilingual aliases are used to identify the
    // language
    private $itsUseUniqueMultilingualAliases = FALSE;
    // The query separator to use for generating HTML URLs
    private $itsOutputQuerySeparator;
    // The query separator to use for generating plain text URLs
    private $itsInputQuerySeparator;
    // monolingual doc ids
    private $itsMonolingualDocIds = array();
    // This defines the default template variables types
    // to associate with the document variables managed by
    // YAMS.
    private $itsDocVarTypes = array(
      'pagetitle' => 'text'
      , 'longtitle' => 'text'
      , 'description' => 'text'
      , 'alias' => 'text'
      , 'introtext' => 'textarea'
      , 'menutitle' => 'text'
      , 'content' => 'richtext'
      );
    // An array of multilingual document variables managed by
    // YAMS. This is defined from the array keys of itsDocVarTypes on
    // initialisation.
    private $itsDocVarNames = NULL;
    // Save this once it is calculated...
    private $itsIsValidMultilingualDocument = NULL;
    private $itsRequestLangId = FALSE;
    // An array containing the document aliases in the different languages
    private $itsDocAliases = array();
    // An array containing the parentId of each document
    private $itsDocParentIds = array();
    // An array containing the isfolder attribute of each document
    private $itsDocIsContainer = array();
    // An array containing the suffix for each document if mime-dependent
    // suffixes are being used
    private $itsDocSuffixes = array();
    // Whether or not the document has been loaded from the cache
    private $itsFromCache = FALSE;
    // A hash of the content
    private $itsLastContentHash = NULL;

    // --
    // -- Singleton stuff
    // --

    // A private constructor; prevents direct creation of object
    private function __construct( )
    {
      $this->Initialise( );
    }

    // The singleton method
    public static function GetInstance( )
    {
      if ( ! isset( self::$theirInstance ) )
      {
        $c = __CLASS__;
        self::$theirInstance = new $c( );
      }

      return self::$theirInstance;
    }

    //   // Prevent instance construction and cloning
    //   protected final function __construct()
    //   {
    //     throw new Exception('YAMS is a singleton class. Get an instance via YAMS::GetInstance(), not new YAMS().');
    //   }
    private final function __clone()
    {
      throw new Exception('Clone is not allowed on singleton (YAMS).');
    }

    // Hold an instance of the class
    private static $theirInstance;

  }
}

?>