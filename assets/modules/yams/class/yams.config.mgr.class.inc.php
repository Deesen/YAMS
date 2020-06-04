<?php
/**
 * Manages the YAMS config file and data.
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 */

require_once('yams.utils.class.inc.php');
require_once('singleton.abstract.class.inc.php');

abstract class YamsConfigMgrAbstract extends Singleton
{

  public function GetVersion()
  {
    // This represents the format of the config file.
    return '1.0';
  }

  protected function Initialise()
  {
    @include( dirname( __FILE__ ) . '/../yams.config.inc.php');

    $this->itsDocVarNames = array_keys( $this->itsDocVarTypes );

    // Check if UTF-8 is being used
    // (Assume the encoding of the web page output
    // is the same as the encoding of the manager)
    YamsUtils::$itsUTF8Modifier = $this->itsEncodingModifierMode;
  }

  public static function GetInstance()
  {
    parent::GetSingletonInstance(__CLASS__);
  }

  public function SaveCurrentSettings()
  {

    $contents = '<?php' . PHP_EOL;

    //----------------------------
    // Protect against PHP errors
    // should this file be accessed
    // on its own...
    //----------------------------

    $contents .=
      '  // Protection against outside access' . PHP_EOL
      . '  if ( ! isset( $this ) )' . PHP_EOL
      . '  {' . PHP_EOL
      . '    return;' . PHP_EOL
      . '  }' . PHP_EOL;

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
        . YamsUtils::AsPHPSingleQuotedString( $langId )
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
        . YamsUtils::AsPHPSingleQuotedString( $langId )
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
      . '  $this->itsDefaultLangId = '
      . YamsUtils::AsPHPSingleQuotedString( $this->itsDefaultLangId )
      . ';' . PHP_EOL;

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
        . YamsUtils::AsPHPSingleQuotedString( $langId )
        . ' => '
        . YamsUtils::AsPHPSingleQuotedString( $rootName )
        . PHP_EOL;
    }
    $contents .= '  );' . PHP_EOL;

    //----------------------------
    // itsMonoServerName
    //----------------------------
    $contents .=
      '  // The monolingual page server name' . PHP_EOL
      . '  $this->itsMonoServerName = '
      . YamsUtils::AsPHPSingleQuotedString( $this->itsMonoServerName )
      . ';' . PHP_EOL;

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
        . YamsUtils::AsPHPSingleQuotedString( $langId )
        . ' => '
        . YamsUtils::AsPHPSingleQuotedString( $serverName )
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
        . YamsUtils::AsPHPSingleQuotedString( $langId )
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
          . YamsUtils::AsPHPSingleQuotedString( $innerLangId )
          . ' => '
          . YamsUtils::AsPHPSingleQuotedString( $langName )
          . PHP_EOL;
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
        . YamsUtils::AsPHPSingleQuotedString( $langId )
        . ' => '
        . YamsUtils::AsPHPSingleQuotedString( $text )
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
        . YamsUtils::AsPHPSingleQuotedString( $langId )
        . ' => array(' . PHP_EOL;
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
          . YamsUtils::AsPHPSingleQuotedString( $tag )
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
        . YamsUtils::AsPHPSingleQuotedString( $langId )
        . ' => '
        . YamsUtils::AsPHPSingleQuotedString( $modxLangName )
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
      . '  $this->itsEncodingModifierMode = '
      . YamsUtils::AsPHPSingleQuotedString( $this->itsEncodingModifierMode )
      . ';' . PHP_EOL;

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
            . YamsUtils::AsPHPSingleQuotedString( $tv )
            . PHP_EOL;
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
      . '  $this->itsLangQueryParam = '
      . YamsUtils::AsPHPSingleQuotedString( $this->itsLangQueryParam )
      . ';' . PHP_EOL;

    //----------------------------
    // itsChangeLangQueryParam
    //----------------------------
    $contents .=
      '  // The yams change lang query parameter name' . PHP_EOL
      . '  $this->itsChangeLangQueryParam = '
      . YamsUtils::AsPHPSingleQuotedString( $this->itsChangeLangQueryParam )
      . ';' . PHP_EOL;

    //----------------------------
    // itsRedirectionMode
    //----------------------------
    $contents .=
      '  // Turn on/off redirection from existing pages to multilingual pages' . PHP_EOL
      . '  // You can set to false if you are developing a site from scratch' . PHP_EOL
      . '  // - although leaving as TRUE does not harm in this instance' . PHP_EOL
      . '  // Set to TRUE if you are converting a website' . PHP_EOL
      . '  // that has already been made public' . PHP_EOL
      . '  $this->itsRedirectionMode = '
      . YamsUtils::AsPHPSingleQuotedString( $this->itsRedirectionMode )
      . ';' . PHP_EOL;

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
      . '  $this->itsMODxSubdirectory = '
      . YamsUtils::AsPHPSingleQuotedString( $this->itsMODxSubdirectory )
      . ';' . PHP_EOL;

    //----------------------------
    // itsURLConversionMode
    //----------------------------
    $contents .=
      '  // The URL conversion mode' . PHP_EOL
      . '  // none: Don\'t do any automatic conversion of MODx URLs.' . PHP_EOL
      . '  // default: Convert MODx URLs surrounded by double quotes to (yams_doc:id) placeholders' . PHP_EOL
      . '  // resolve: Convert MODx URLs surrounded by double quotes to (yams_docr:id) placeholders' . PHP_EOL
      . '  $this->itsURLConversionMode = '
      . YamsUtils::AsPHPSingleQuotedString( $this->itsURLConversionMode )
      . ';' . PHP_EOL;

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
        . YamsUtils::AsPHPSingleQuotedString( $mimeType )
        . ' => '
        . YamsUtils::AsPHPSingleQuotedString( $suffix )
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
        . YamsUtils::AsPHPSingleQuotedString( $langId )
        . ' => '
        . YamsUtils::AsPHPSingleQuotedString( $roles )
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
      '  // An array of doc ids for which URLs of the form index.php?id= ... will be' . PHP_EOL
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

    if(!is_writable(dirname( __FILE__ ) . '/../yams.config.inc.php')) return FALSE;

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

  // -------------------------
  // Getters
  // -------------------------

  public function GetActiveLangIds()
  {
    return $this->itsActiveLangIds;
  }

  public function GetInactiveLangIds()
  {
    return $this->itsInactiveLangIds;
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

  public function GetDefaultLangId()
  {
    return $this->itsDefaultLangId;
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
      return YamsUtils::UrlEncode( $this->itsRootName[ $langId ] );
    }
    else
    {
      return $this->itsRootName[ $langId ];
    }
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

  public function GetLangNames()
  {
    return $this->itsLangNames;
  }

  public function GetChooseLangText( $langId )
  {
    if ( ! array_key_exists( $langId, $this->itsChooseLangText ) )
    {
      return '';
    }
    return $this->itsChooseLangText[ $langId ];
  }

  public function GetPrimaryLangTag( $langId )
  {
    if ( ! array_key_exists( $langId, $this->itsLangTags ) )
    {
      return '';
    }
    return $this->itsLangTags[ $langId ][ 0 ];
  }

  public function GetLangTagsText( $langId )
  {
    if ( !array_key_exists( $langId, $this->itsLangTags ) )
    {
      return '';
    }
    return implode(',', $this->itsLangTags[ $langId ] );
  }

  public function GetMODxLangName( $langId  )
  {
    if ( ! array_key_exists( $langId, $this->itsMODxLangName ) )
    {
      return '';
    }
    return $this->itsMODxLangName[ $langId ];
  }

  public function GetActiveTemplates()
  {
    return array_keys( $this->itsActiveTemplates );
  }

  public function GetActiveTemplatesList()
  {
    return implode( ',', array_keys( $this->itsActiveTemplates ) );
  }

  public function GetManageTVs()
  {
    return $this->itsManageTVs;
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

  public function GetRedirectionMode()
  {
    return $this->itsRedirectionMode;
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

  public function GetShowSiteStartAlias()
  {
    return $this->itsShowSiteStartAlias;
  }

  public function GetRewriteContainersAsFolders()
  {
    return $this->itsRewriteContainersAsFolders;
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
          '/\//' . YamsUtils::UTF8Modifier()
          , $this->itsMODxSubdirectory
          );
      foreach ( $modxSubdirectoryArray as &$part )
      {
        $part = YamsUtils::UrlEncode( $part );
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

  public function GetURLConversionMode()
  {
    return $this->itsURLConversionMode;
  }

  public function GetUseMultilingualAliases()
  {
    return $this->itsUseMultilingualAliases;
  }

  public function GetMultilingualAliasesAreUnique()
  {
    return $this->itsMultilingualAliasesAreUnique;
  }

  public function GetUseMimeDependentSuffixes()
  {
    return $this->itsUseMimeDependentSuffixes;
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
        '/^\!/' . YamsUtils::UTF8Modifier()
        , $rolesList
      ) == 1 )
    {
      $rolesList = preg_replace(
        '/^\!/' . YamsUtils::UTF8Modifier()
        , ''
        , $rolesList );
    }
    else
    {
      $rolesList = '!' . $rolesList;
    }
    return $rolesList;
  }

  public function GetUseStripAlias()
  {
    return $this->itsUseStripAlias;
  }

  public function GetAcceptMODxURLDocIdsString( )
  {
    return implode( ',', $this->itsAcceptMODxURLDocIds );
  }

  // -------------------------
  // Setters
  // -------------------------

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
    if ( $save )
    {
      return $this->SaveCurrentSettings();
    }
    return TRUE;
  }

  public function SetMonoServerName( $name, $save = TRUE )
  {
    if (
      ! is_string( $name )
      || preg_match(
        '/^' . YAMS_RE_SERVER_NAME . '$/i'
        . YamsUtils::UTF8Modifier()
        , $name
        ) != 1)
    {
      return FALSE;
    }
    $this->itsMonoServerName = $name;
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
        . YamsUtils::UTF8Modifier()
        , $name
        ) != 1)
    {
      return FALSE;
    }
    $this->itsMultiServerName[ $langId ] = $name;
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
    if (is_countable($parsedLangTags[1]) && ( count( $parsedLangTags[1] ) < 1 ))
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

  public function SetActiveTemplates( $activeTemplates, $save = TRUE )
  {
    if ( !is_array( $activeTemplates ) )
    {
      return FALSE;
    }
    foreach ( $activeTemplates as $templateId => $activeTVs )
    {
      if ( ! YamsUtils::IsValidId( $templateId ) )
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

  public function SetRedirectionMode( $redirectionMode, $save = TRUE )
  {
    if ( ! self::IsValidRedirectionMode( $redirectionMode ) )
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
          . YamsUtils::UTF8Modifier()
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

  public function SetURLConversionMode( $urlConversionMode, $save = TRUE )
  {
    if ( ! self::IsValidURLConversionMode( $urlConversionMode ) )
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
          . YamsUtils::UTF8Modifier()
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

  public function SetAcceptMODxURLDocIdsString( $acceptMODxURLDocIdsString, $save = TRUE )
  {
    if ( !is_string( $acceptMODxURLDocIdsString ) )
    {
      return FALSE;
    }
    $newAcceptMODxURLDocIds = preg_split(
      '/\s*,\s*/x'
        . YamsUtils::UTF8Modifier()
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
      if ( YamsUtils::IsValidId( $docId ) )
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

  // -------------------------
  // Removers
  // -------------------------

  protected function RemoveActiveLangId(
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

  protected function RemoveInactiveLangId( $langId, $save = FALSE )
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

  protected function RemoveIsLTR( $langId, $save = FALSE )
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

  protected function RemoveRootName( $langId, $save = FALSE )
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

  protected function RemoveServerName( $langId, $save = FALSE )
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

  protected function RemoveLangNames( $inlangId, $save = FALSE )
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

  protected function RemoveChooseLangText( $langId, $save = FALSE )
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

  protected function RemoveLangTagsText( $langId, $save = FALSE )
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

  protected function RemoveMODxLangName( $langId, $save = FALSE )
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

  // -------------------------
  // Adders
  // -------------------------

  protected function AddActiveLangId(
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

  protected function AddInactiveLangId(
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
  // -------------------------
  // Is'ers
  // -------------------------

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

  public static function IsValidRedirectionMode( $redirectionMode )
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

  public static function IsValidURLConversionMode( $urlConversionMode )
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

  // -------------------------
  // Others
  // -------------------------

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

  // --
  // -- Default Attributes
  // -- These can be overriden using the yams.config.inc.php file
  // --
  // A list of active lang ids
  protected $itsActiveLangIds = array(
    'en'
    );
  // A list of inactive lang ids
  protected $itsInactiveLangIds = array(
    'fr'
    , 'ja'
    , 'de'
    , 'ru'
    );
  // Specifies the language direction, ltr or rtl
  protected $itsIsLTR = array(
    'en' => TRUE
    , 'fr' => TRUE
    , 'ja' => TRUE
    , 'de' => TRUE
    , 'ru' => TRUE
    );
  // The default language id
  protected $itsDefaultLangId = 'en';
  // The name of the root folder
  // eg: http://mysite.com/rootfolder
  // use empty string for no folder
  protected $itsRootName = array(
    'en' => 'en'
    , 'fr' => 'fr'
    , 'ja' => 'ja'
    , 'de' => 'de'
    , 'ru' => 'ru'
    );
  // Use to define the server name for monolingual webpages
  // Use empty string for default server name ( as provided by [(site_url)] )
  protected $itsMonoServerName = '';
  // Use to set the server name by language
  // No protocol and no trailing slash.
  // eg: www.mylanguage.mysite.com
  // use empty string for the default server name
  protected $itsMultiServerName = array(
    'en' => ''
    , 'fr' => ''
    , 'ja' => ''
    , 'de' => ''
    , 'ru' => ''
    );
  // The name of the language in the default lang
  // and any other languges.
  protected $itsLangNames = array(
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
  protected $itsChooseLangText = array(
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
  protected $itsLangTags = array(
    'en' => array( 'en' )
    , 'fr' => array( 'fr' )
    , 'ja' => array( 'ja' )
    , 'de' => array( 'de' )
    , 'ru' => array( 'ru' )
    );
  // The modx language name, or the empty string if none set.
  protected $itsMODxLangName = array(
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
  protected $itsEncodingModifierMode = 'manager';
  // a comma separated list of active template ids
  // if the default activity is none
  protected $itsActiveTemplates = array();
  // Whether or not to sync template variables
  protected $itsManageTVs = TRUE;
  // The yams lang query parameter name
  protected $itsLangQueryParam = 'yams_lang';
  // The yams change lang query/post parameter name
  protected $itsChangeLangQueryParam = 'yams_new_lang';
  // The redirection mode. Can be:
  // none: No redirection
  // default: Redirect to the equivalent default language page
  // browser: Redirect the a browser language if available,
  //          else the default language page.
  protected $itsRedirectionMode = 'default';
  // The type of http redirection to perform when redirecting to the default
  // language
  protected $itsHTTPStatus = 307;
  // The type of http redirection to perform when redirecting to a non-default
  // language
  protected $itsHTTPStatusNotDefault = 303;
  // The type of http redirection to perform when responding to a request to change language
  // language
  protected $itsHTTPStatusChangeLang = 303;
  // Whether or not to hide the original fields
  // For use with manager manager
  protected $itsHideFields = FALSE;
  // Whether or not to place tvs for individual languages on separate tabs
  // For use with manager manager
  protected $itsTabifyLangs = TRUE;
  // Whether or not to synchronise the document pagetitle with the default language pagetitle
  protected $itsSynchronisePagetitle = FALSE;
  // Whether or not to use EasyLingual compatibility mode
  protected $itsEasyLingualCompatibility = FALSE;
  // Whether or not to show the site_start document alias.
  protected $itsShowSiteStartAlias = TRUE;
  // Whether or not to rewrite containers as folders.
  protected $itsRewriteContainersAsFolders = FALSE;
  // If MODx is installed into a subdirectory then this param
  // can be used to specify the path to that directory.
  // (with a trailing slash and no leading slash)
  protected $itsMODxSubdirectory = '';
  // The URL conversion mode
  // none: Don't do any automatic conversion of MODx URLs.
  // default: Convert MODx URLs surrounded by double quotes to (yams_doc:id) placeholders
  // resolve: Convert MODx URLs surrounded by double quotes to (yams_docr:id) placeholders
  // The default is doc, which replicates standard MODx behaviour, but
  // docr might be more useful.
  protected $itsURLConversionMode = 'default';
  // Whether or not to use multilingual aliases
  protected $itsUseMultilingualAliases = FALSE;
  // Whether multilingual aliases will be unique
  // If TRUE, the default when creating new aliases is langId-documentalias
  // If FALSE, the default when creating new aliases documentalias
  protected $itsMultilingualAliasesAreUnique = FALSE;
  // Whether or not to determine the document suffix based on mime type
  protected $itsUseMimeDependentSuffixes = FALSE;
  // The mime suffix mapping
  protected $itsMimeSuffixMap = array(
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
  protected $itsLangRolesAccessMap = array();
  protected $itsUseStripAlias = TRUE;
  // An array of doc ids for which URLs of the form index.php?id= ... will be
  // accepted - even if friendly aliases are being used.
  protected $itsAcceptMODxURLDocIds = array();

  // This defines the default template variables types
  // to associate with the document variables managed by
  // YAMS.
  protected $itsDocVarTypes = array(
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
  protected $itsDocVarNames = NULL;
    
}
  
if ( ! class_exists('YamsConfigMgr') )
{
  class YamsConfigMgr extends YamsConfigMgrAbstract
  {
    protected function Initialise()
    {
      parent::Initialise();
    }
    
    public static function GetInstance()
    {
      return parent::GetSingletonInstance(__CLASS__);
    }

  }  
}

?>