<?php
/**
 * Utility functions for use with YAMS
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 */
if ( ! class_exists( 'YamsUtils' ) )
{
  class YamsUtils
  {

    // This can be set to overrider the default behaviour
    // which is to check the MODx charset to determine whether
    // to use the UTF-8 preg encoding modifier
    public static $itsUTF8Modifier = NULL;

    public static function IsUTF8()
    {
      global $modx;      
      if ( $modx->config['modx_charset'] == 'UTF-8')
      {
        return TRUE;
      }
      return FALSE;      
    }

    public static function CharSet()
    {
      global $modx;
      return $modx->config['modx_charset'];
    }

    public static function UTF8Modifier()
    {
      global $modx;
      switch( self::$itsUTF8Modifier )
      {
        case '':
        case 'u':
          return self::$itsUTF8Modifier;
        default:
          if ( self::IsUTF8() )
          {
            return 'u';
          }
          return '';        
      }
    }

    public static function StripControlCodes( $string )
    {
      // This strips control codes from a unicode encoded string
      // In unicode, the 65 control code characters have the following
      // code points (http://www.unicode.org/versions/Unicode5.2.0/ch16.pdf):
      // 0...31     or 00...1F     C0 controls (http://www.unicode.org/charts/PDF/U0000.pdf)
      // 127        or 7F          delete      (http://www.unicode.org/charts/PDF/U0000.pdf)
      // 128...159  or 80...9F     C1 controls (http://www.unicode.org/charts/PDF/U0080.pdf)

      // See...
      // http://www.php.net/manual/en/regexp.reference.unicode.php

      // $success = preg_replace('/\p{Cc}/u', '', $string);
      $cleanedString = preg_replace(
        '/[\x00-\x1F\x7F\x80-\x9F]/' . self::UTF8Modifier(),
        '',
        $string
        );
      
      if ( is_null( $cleanedString ) )
      {
        return FALSE;
      }

      return $cleanedString;

    }

    public static function CountChars( $string )
    {
      global $modx;
      
      // This function counts the number of characters (not bytes) in a string.
      // strlen returns the number of bytes.
      $encoding = $modx->config['charset'];

      if ( $encoding != 'UTF-8' )
      {
        // non-UTF-8 requires libiconv, which comes with PHP 5 by default
        $string = iconv( $encoding, 'UTF-8', $string );
        if ( $string === FALSE )
        {
          throw new Exception('Utils: Could not convert encoding');
        }
      }

      return preg_match_all( '/./us', $string, $matches );
    }
    
    public static function IsValidUTF8(
      &$string
      , $replacementString = ' '
      , &$nChars = 0
      )
    {
      // This follows section 3.9 (Unicode Encoding Forms, UTF-8)
      // of the latest unicode standard.

      // Specifically, Table 3-7. Well-formed UTF-8 byte sequences

      // Range codepoint          1st-byte   2nd-byte   3rd-byte    4th-byte
      //   0   0...127            0...127
      //   1   128...2047         194...223  128...191
      //   2   2048...4095           224     160...191  128...191
      //   3   4096...53247       225...236  128...191  128...191
      //   4   53248...55295         237     128...159  128...191
      //   5   57344...65535      238...239  128...191  128...191
      //   6   65536...262143        240     144...191  128...191  128...191
      //   7   262144...1048575   241...243  128...191  128...191  128...191
      //   8   1048576...1114111     244     128...143  128...191  128...191

      $nBytesInReplacement = strlen( $replacementString );
      $nCharsInReplacement = self::CountChars( $replacementString );
      $lengthInBytes = strlen( $string );

      $nChars = 0;
      $isValidUTF8 = TRUE;

      $currentByteInChar = 0;
      $currentRange = 1;

      if ( $lengthInBytes == 0 )
      {
        return $isValidUTF8;
      }

      $i = 0;
      for ( $i=0; $i < $lengthInBytes; $i++ )
      {
        $byte = ord( $string[$i] );

        $currentByteInChar += 1;

        switch ( $currentByteInChar )
        {
          case 1:
            // determine range from first byte...
            if ( 0 <= $byte && $byte <= 127 )
            {
              $currentRange = 0;
              $currentByteInChar = 0;
            }
            elseif ( 194 <= $byte && $byte <= 223 )
            {
              $currentRange = 1;
            }
            elseif ( $byte == 224 )
            {
              $currentRange = 2;
            }
            elseif ( 225 <= $byte && $byte <= 236 )
            {
              $currentRange = 3;
            }
            elseif ( $byte == 237 )
            {
              $currentRange = 4;
            }
            elseif ( 238 <= $byte && $byte <= 239 )
            {
              $currentRange = 5;
            }
            elseif ( $byte == 240 )
            {
              $currentRange = 6;
            }
            elseif ( 241 <= $byte && $byte <= 243 )
            {
              $currentRange = 7;
            }
            elseif ( $byte == 244 )
            {
              $currentRange = 8;
            }
            else
            {
              // Invalid byte sequence encountered!!!
              $isValidUTF8 = FALSE;
              // Replace the byte sequence...
              $string = substr_replace( $string, $replacementString, $i-$currentByteInChar + 1, $currentByteInChar );
              // Adjust length of string
              $lengthInBytes += $nBytesInReplacement - $currentByteInChar;
              // Fast forward
              $i += $nBytesInReplacement - $currentByteInChar;
              $nChars += $nCharsInReplacement - 1;
              $currentByteInChar = 0;
              break;
            }
            break;
          case 2:
            // Check the value based on the current range...
            $validByte = TRUE;
            switch ( $currentRange )
            {
              case 1:
                if ( $byte < 128 || $byte > 191 )
                {
                  $validByte = FALSE;
                }
                else
                {
                  // This must be the last byte in the sequence...
                  $currentByteInChar = 0;
                }
                break;
              case 2:
                if ( $byte < 160 || $byte > 191 )
                {
                  $validByte = FALSE;
                }
                break;
              case 3:
                if ( $byte < 128 || $byte > 191 )
                {
                  $validByte = FALSE;
                }
                break;
              case 4:
                if ( $byte < 128 || $byte > 159 )
                {
                  $validByte = FALSE;
                }
                break;
              case 5:
                if ( $byte < 128 || $byte > 191 )
                {
                  $validByte = FALSE;
                }
                break;
              case 6:
                if ( $byte < 144 || $byte > 191 )
                {
                  $validByte = FALSE;
                }
                break;
              case 7:
                if ( $byte < 128 || $byte > 191 )
                {
                  $validByte = FALSE;
                }
                break;
              case 8:
                if ( $byte < 128 || $byte > 143 )
                {
                  $validByte = FALSE;
                }
                break;
              default:
                throw new Exception('Should not have got here!');
            }
            if ( ! $validByte )
            {
              // Invalid byte sequence encountered!!!
              $isValidUTF8 = FALSE;
              // Replace the byte sequence...
              $string = substr_replace( $string, $replacementString, $i-$currentByteInChar + 1, $currentByteInChar );
              // Adjust length of string
              $lengthInBytes += $nBytesInReplacement - $currentByteInChar;
              // Fast forward
              $i += $nBytesInReplacement - $currentByteInChar;
              $nChars += $nCharsInReplacement - 1;
              $currentByteInChar = 0;
              break;
            }
            break;
          case 3:
            switch ( $currentRange )
            {
              case 2:
              case 3:
              case 4:
              case 5:
                // This must be the last byte in the sequence
                $currentByteInChar = 0;
                break;
              case 6:
              case 7:
              case 8:
                break;
              default:
                throw new Exception('Should not have got here!');
            }
            if ( $byte < 128 || $byte > 191 )
            {
              // Invalid byte sequence encountered!!!
              $isValidUTF8 = FALSE;
              // Replace the byte sequence...
              $string = substr_replace( $string, $replacementString, $i-$currentByteInChar + 1, $currentByteInChar );
              // Adjust length of string
              $lengthInBytes += $nBytesInReplacement - $currentByteInChar;
              // Fast forward
              $i += $nBytesInReplacement - $currentByteInChar;
              $nChars += $nCharsInReplacement - 1;
              $currentByteInChar = 0;
              break;
            }
            break;
          case 4:
            switch ( $currentRange )
            {
              case 6:
              case 7:
              case 8:
                $currentByteInChar = 0;
                break;
              default:
                throw new Exception('Should not have got here!');
            }
            if ( $byte < 128 || $byte > 191 )
            {
              // Invalid byte sequence encountered!!!
              $isValidUTF8 = FALSE;
              // Replace the byte sequence...
              $string = substr_replace( $string, $replacementString, $i-$currentByteInChar + 1, $currentByteInChar );
              // Adjust length of string
              $lengthInBytes += $nBytesInReplacement - $currentByteInChar;
              // Fast forward
              $i += $nBytesInReplacement - $currentByteInChar;
              $nChars += $nCharsInReplacement - 1;
              $currentByteInChar = 0;
              break;
            }
            break;
          default:
            throw new Exception('Should not have got here!');
        }

        if ( $currentByteInChar == 0 )
        {
          $nChars += 1;
        }

      }

      return $isValidUTF8;

    }
    
    public static function Escape(
      $string
      , $doubleEncode = TRUE
      , $enforceWellFormedUTF8 = TRUE
      , $stripControlCodes = TRUE
    )
    {

      // Escapes a string so that it can be embedded safely as data (not markup)
      // within html or xml
      // By default will remove invalid characters from UTF-8
      // It will also remove any control characters.
      
      if (
        self::IsUTF8()
        && $enforceWellFormedUTF8
      )
      {
        self::IsValidUTF8( $string );
      }
      
      if ( $stripControlCodes )
      {
        $string = self::StripControlCodes( $string );
        if ( $string === FALSE )
        {
          throw new Exception('Problem encountered while removing control codes from string.');
        }
      }

      if ( version_compare( PHP_VERSION, '5.2.3' ) >= 0 )
      {
        return htmlspecialchars(
            $string
            , ENT_QUOTES
            , self::CharSet()
            , $doubleEncode
          );
      }
      else
      {
        // Don't use the final argument...
        if ( ! $doubleEncode )
        {

          // unencode the existing special characters.
          // they will be re-encoded...
          $string = htmlspecialchars_decode(
              $string
              , ENT_QUOTES
            );
        }
        return htmlspecialchars(
            $string
            , ENT_QUOTES
            , self::CharSet()
          );
      }
    }

    public static function Unescape( $string )
    {
      return html_entity_decode(
        $string
        , ENT_QUOTES
        , self::CharSet()
        );
    }
    
    public static function Clean(
      $string
      , $doubleEncode = TRUE
      , $isHTML = TRUE
      , $enforceWellFormedUTF8 = TRUE
      , $stripControlCodes = TRUE
      )
    {
      //
      // This first removes any invalid unicode characters in the string
      // (if UTF-8)
      // It then removes any control characters.
      //
      // It then strips any php, html or xml from the string.
      // It then escapes the string so that it can be embedded safely
      // within html or xml

      if ( self::IsUTF8() && $enforceWellFormedUTF8 )
      {
        self::IsValidUTF8( $string );
      }

      if ( $stripControlCodes )
      {
        $string = self::StripControlCodes( $string );
        if ( $string === FALSE )
        {
          throw new Exception('Problem encountered while removing control codes from string.');
        }
      }

      if ( $isHTML )
      {
        return self::Escape( strip_tags( $string ), $doubleEncode, FALSE, FALSE );
      }
      else
      {
        return strip_tags( self::Unescape( strip_tags( $string ) ) );
      }
    }

    public static function GetFileContents( $filename )
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

    public static function UrlEncode( $string, $encode = TRUE )
    {
      // To properly encode an url
      // 1) Encode string in UTF-8
      // 2) rawurlencode
      //
      // $_GET is automatically rawurldecoded... but it is left
      // in UTF-8 as far as I know, since PHP doesn't know what else
      // to do with it.

      $modxCharset = self::CharSet();

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

    public static function UrlDecode( $string, $decode = FALSE )
    {
      // $_GET is automatically rawurldecoded... but it is left
      // in UTF-8 as far as I know, since PHP doesn't know what else
      // to do with it.
      //
      // So, don't need to rawurldecode it,
      // but do need to convert it to the correct character encoding.

      $modxCharset = self::CharSet();

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

    public static function GetGET()
    {
      // Gets a correctly encoded version of the GET array
      $arr = array();
      foreach ( $_GET as $key => $value )
      {
        $arr[self::UrlDecode($key)] = self::UrlDecode($value);
      }
      return $arr;
    }

    public static function GetPOST()
    {
      // Gets a correctly encoded version of the POST array
      $arr = array();
      foreach ( $_POST as $key => $value )
      {
        $arr[self::UrlDecode($key)] = self::UrlDecode($value);
      }
      return $arr;
    }

    public static function IsValidId( $id )
    {
      return ctype_digit( (string) $id );
    }

    public static function IsHTTPS()
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

    public function IsValidLangGroupId( $langId )
    {
      return (
        preg_match(
          '/^[a-zA-Z0-9]+$/D'
          . self::UTF8Modifier()
          , $langId
          )
        == 1
        );
    }

    public static function AsPHPSingleQuotedString(
      $string
      , $stripControlCodes = TRUE
      )
    {
      // http://www.php.net/manual/en/language.types.string.php#language.types.string.syntax.single
      // Replace ' by \' and surroung in single quotes.
      // This can be used for writing a string as PHP code.

      if ( $stripControlCodes )
      {
        // remove control codes for security...
        $string = self::StripControlCodes( $string );
        if ( $string === FALSE )
        {
          throw new Exception('Could not strip control codes from string.');
        }
      }

      $escapedString = preg_replace(
        '/\'/' . self::UTF8Modifier()
        , '\\\''
        , $string
        );
      if ( is_null( $escapedString ) )
      {
        return FALSE;
      }
      // Then double any backslaches preceding a \'
      $escapedString = preg_replace_callback(
        '/([\\\]+)(\\\\\')/' . self::UTF8Modifier()
        , create_function(
          '$matches'
          , 'return preg_replace(\'/\\\\\/u\', \'\\\\\\\\\\\\\', $matches[1]). $matches[2];'
          )
        , $escapedString );
      if ( is_null( $escapedString ) )
      {
        return FALSE;
      }
      // Then replace a trailing \  by \\
      $escapedString = preg_replace(
        '/\\\$/' . self::UTF8Modifier()
        , '\\\\\\\\'
        , $escapedString
        );
      if ( is_null( $escapedString ) )
      {
        return FALSE;
      }
      // Then wrap in single quotes
      return '\'' . $escapedString . '\'';
    }

    public static function PregQuoteReplacement( $str )
    {
      // See
      // http://www.procata.com/blog/archives/2005/11/13/two-preg_replace-escaping-gotchas/
      return preg_replace(
        '/(\$|\\\\)(?=\d)/' . self::UTF8Modifier()
        , '\\\\\1'
        , $str);
    }

  }
  
}

?>