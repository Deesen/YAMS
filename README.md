YAMS: Yet Another Multilingual Solution
---------------------------------------

Version: 1.1.9
Author: PMS
        The original multilingual alias code was written by mgbowman.
Date: 2010/05/21

** Please check that the plugin is activated on the correct events ***

Notes: This is a bug fix release and at the time of release is the most stable
version. All users are recommended to upgrade to this version.

1. Notes
2. Pre-requisites
3. Upgrade/Update Instructions
4. Installation Instructions
5. ManagerManager Setup
6. PHx Setup
7. History

1. Notes
--------

YAMS is a highly configurable extension to MODx that is designed to make it easy
to develop multilingual websites. The following features are currently implemented:

- All content is managed via a single document tree to enable a consistent site
  structure across all language variants.
- Standard MODx syntax can be used within document templates.
- A tabbed language layout for multilingual documents (requires ManagerManager)
- Document templates can be configured as multilingual or monolingual.
- Highly configurable multilingual URLs. The following are examples of different
  ways YAMS could be set-up to refer to language variants of a single document:

  Multilingual aliases:
    * http://server_name/my-doc-en.html
    * http://server_name/mon-doc-fr.html

  Server name mode only:
    * http://en.server_name.com/mydoc.html
    * http://fr.server_name.com/mydoc.html

  Root name mode only:
    * http://server_name.com/en/mydoc.html
    * http://server_name.com/fr/mydoc.html

  Root name mode only, with one language at root:
    * http://server_name.com/mydoc.html
    * http://server_name.com/fr/mydoc.html

  Server name mode, root name mode, friendly alias paths, multilingual aliases
  and multibyte URLs:
    * http://en.server_name.com/england/folder/mydoc.html
    * http://fr.server_name.com/la-france/répertoire/mon-doc.html

- Additional URL configurability, including ability to hide alias of site start
  document, SEO friendly redirection, multibyte URLs and content-type dependent
  alias suffixes.
- Additional YAMS Placeholders allowing access to language specific settings,
  such as language name and direction.
- Additional functionality via the YAMS snippet call, including the ability to
  manage custom multilingual chunks, snippets and template variables, to generate
  list-based or drop-down based language switchers (templatable), the ability
  to repeat content in multiple languages...
- Extensions for Ditto, Wayfinder, Jot and eForm.
- Possible to create custom multilingual template variables.

2. Pre-requisites
-----------------

YAMS has been developed on MODx v0.9.6.3 and with PHP >= 5.2.6-3.
It will not work on servers running PHP 4.

ManagerManager is not required for YAMS to function, but is recommended. YAMS
can use ManagerManager to hide redundant document variables and organise the
language fields in the document view.

3. Upgrade/Update Instructions
------------------------------

To upgrade/update from a previous version do the following:

1. Rename your assets/modules/yams directory, to something else. For example
   assets/modules/yams_old or assets/modules/yams_v1.1.x
2. Copy the new yams directory to assets/modules/yams
3. Copy your yams.config.inc.php file from your old yams directory into your
   new yams directory.
4. Make sure that your new yams directory and the yams.config.inc.php file (if
   it exists) are writeable by your server user/group.
5. Make sure that your YAMS plugin is set-up to be active on all the events
   described in the installation instructions below.
6. Check that YAMS always appears first in your plugin execution order for
   each event that it is active. In particular, if you have phx installed then
   YAMS should appear before it in the OnParseDocument execution order.
7. Check that everything is working and that your settings are correctly
   displayed in the YAMS module. If so, you may remove your old yams directory.
   If there are any problems, then you can simply roll back be renaming your
   directories to reinstate your previous yams directory.

4. Installation Instructions
----------------------------

1. Copy the yams directory to assets/modules/yams

2. Make sure that the assets/modules/yams directory is writeable by the
user/group that your server runs under. YAMS maintains a config file called
config.inc.php in the directory that is automatically updated via the module
interface.

3. Within MODx under Elements > Manage Elements > Plugins create a new plugin:

Plugin name:
  YAMS
Description:
  Yet Another Multilingual Solution Plugin
Plugin code:
  require( $modx->config['base_path'] . 'assets/modules/yams/yams.plugin.inc.php');
System Events:
  - OnLoadWebDocument
  - OnParseDocument
  - OnWebPageInit
  - OnWebPagePrerender
  - OnLoadWebPageCache
  - OnPageNotFound
  - OnBeforeDocFormSave
  Note that YAMS should be moved to first place in the execution order for all
  events to which it is associated.

4. Within MODx under Elements > Manage Elements > Snippets create a new snippet:

Snippet name:
  YAMS
Description:
  Gets multi-language content.
Snippet code:
  // The following line needs to be placed between the opening and closing php
  // markers
  require( $modx->config['base_path'] . 'assets/modules/yams/yams.snippet.inc.php' );

5. Within MODx under Modules>Manage Modules create a new module:

Module name:
  YAMS
Description:
  Yet Another Multilingual Solution
Module code:
  require_once( $modx->config['base_path'] . 'assets/modules/yams/yams.module.inc.php' );

6. Reload the page to update the manager view. If you want to use ManagerManager
to obtain a tabbed document interface then follow the instructions below (point
5.) to set it up.

7. Go to Modules > YAMS and follow the instructions on the Documentation > Setup
tab to set-up your multilingual site.

5. ManagerManager Setup
-----------------------

To set up ManagerManager so that it provides a tabbed document interface, please
do the following:

1. Check that the ManagerManager plugin is installed under
   Elements > Manage Elements > Plugins. If not, it can be obtained from the
   MODx repository:
   http://modxcms.com/extras/package/?package=255
   The latest version is generally recommended, but please keep an eye on the
   forums for reports of any problems.
2. Modify the ManagerManager plugin configuration so that it knows to find
   custom ManagerManager rules in a chunk called mm_rules. In newer versions
   this can be set using the configuration tab. In older versions this is done
   by including the line
   $config_chunk = 'mm_rules';
   in the plugin code.
3. Under Elements > Manage Elements > Chunks, create a chunk called mm_rules and
   add the following line:

   require( $modx->config['base_path'] . 'assets/modules/yams/yams.mm_rules.inc.php' );

   If you are already using custom ManagerManager rules, then it is advisable to
   place the YAMS require line at the end of the rules.

6. PHx Setup
------------

If using the PHx snippet then please note the following. For some reason, a file
specified using include_once gets reincluded and this causes the PHxParser class
to be redefined, which generates a PHP parse error. This can avoided by modifying
the PHx snippet to wrap the include in some code that will only include the file
if the class has not yet been defined:

if ( ! class_exists( 'PHxParser' ) )
{
 include_once $modx->config['rb_base_dir'] . "plugins/phx/phx.parser.class.inc.php";
}

Also, please remember that the Plugin Execution Order must be edited to place
YAMS in first place - that is before PHx - on all associated events.

7. History
----------

Version 1.1.9
- Bug Fix: Fixed a bug introduced at version 1.1.8, which breaks ((yams_data:..))
- Bug Fix: Applied kongo09's patch, which fixes a bug whereby default content is
  not copied over to new fields when multilingual tvs are associated with new templates.
  http://modxcms.com/forums/index.php/topic,43821.0.html

Version 1.1.8
- Updated YAMS ManagerManager rules so that when hide fields is on, multilingual
  aliases are hidden when multilingual aliases are switched off and the standard
  document alias is hidden when multilingual aliases are switched on.
- Updated the documentation for Hide Fields accordingly.
- Bug Fix: Fixed a <p> that should have been a </p> in the module
- Updated the forum link to http://modxcms.com/forums/index.php/board,381.0.html
- Added a title field to the YAMS ditto extender. This outputs the page title.
- Bug Fix: Corrected a typo str2lower -> strtolower.
  This bug fix is necessary for YAMS to work over HTTPS.
  Reported by noes: http://modxcms.com/forums/index.php/topic,42752.0.html
- Added an additional check to prevent crashing if $modx->documentObject doesn't
  exist.
- Made the Expand function public.
- Bug Fix: Fixed a bug whereby the current language would be lost when changing
  page using ditto pagination and unique multilingual aliases.
- Bug Fix: Corrected a problem with switching languages when using unique
  multilingual aliases.
- Improved installation instructions.
- Bug Fix: Fixed a bug whereby YAMS ManagerManager rules would be applied to all
  (rather than no) templates when no multilingual templates are specified.
- Documentation updates

Version 1.1.7 alpha RC7
- Included @French Fries' Wayfinder breadcrumbs templates and updated the How To?
  documentation.
- Included an option to turn off automatic redirection to the correct multilingual
  URL when standard MODx style URLs are encountered for specified document ids.
- Bug fix: MODx default behaviour is that when a document not assigned any alias
  it is instead referred to by its id. This wasn't implemented. Done now, except
  that for multilingual documents with unique aliases on, the documents are
  referred to by langId-docId.
- Removed a system event log command that was added for debugging purposes but
  accidentally left in the code.

Version 1.1.7 alpha RC6
- Most of the languageicons have been removed from the distribution. The full
  set can be downloaded from http://www.languageicon.org/
- Removed the 'do you want to allow YAMS to manager multilingual variables' option
  from the multilingual templates tab.
- Tweaks to the module interface to make it easier to to find submit buttons.
- Removed some unneeded checks in frequently executed code for efficiency
- Bug Fix: Fixed a couple of errors whereby YAMS was trying to access a regexp
  match that was undefined (rather than empty)
- Bug Fix: Fixed an error that could potentially result in YAMS not correctly
  identifying an HTTPS connection.
- Efficiency improvements.
  1) YAMS removes empty constructs instead of processing them.
  2) When loading monolingual documents, now only the default language variant is
     parsed. Previously all language variants were parsed but only one was served.
     Monolingual documents will be served approx 1/n times faster, where n is the
     number of languages.
  3) As soon as a document is loaded from cache YAMS now strips out superfluous
     language variants. Previously it evaluated all language variants but served
     just one. For documents with a lot of uncachable content this can lead to an
     improvement in speed of approx 1/n, where n is the number of languages.
- Bug Fix: Updated the managermanager rules to fix a bug whereby if more than one
  custom tv was assigned to a language, only the last would be tabified.

Version 1.1.7 alpha RC5
- Bug fix: Fixed a bug whereby on first save of a newly created multilingual
  document, pagetitles and aliases would not get updated.
- Bug fix: Fixed an URL encoding issue. The php header function accepts
  a plain text URL, but YAMS was passing it an HTML encoded URL.
- Bug fix: The new multilingual URL capability had broken query parameter mode
  and non-friendly URLs. Fixed.

Version 1.1.7 alpha RC4
- Now, if YAMS doesn't recognise an URL as being valid, but MODx does, then YAMS
  will redirect from the MODx recognised URL to the correct URL using the status
  codes defined on the 'Other Params' tab, rather than generating 404 not found.
  (This aids compatibility with existing MODx resources that don't understand
  multilingual URLs and is what YAMS used to do in previous versions before I broke
  it!)

Version 1.1.7 alpha RC3
- Bug fix: Corrected an .htaccess bug.
  http://modxcms.com/forums/index.php/topic,36513.msg243901.html#msg243901

Version 1.1.7 alpha RC2
- Bug fix: Corrected a small URL encoding bug.
- Included an option to make MODx stripAlias optional for multilingual aliases.
- YAMS now does automatic updating of aliases, checking for unique aliases and
  checking for duplicate aliases.
- Updated the YAMS managermanager rules so that they work with the latest version
  of managermanager (0.3.4), which refers to tvs by name instead of id like MODx.
  YAMS should be backwards compatible with older versions of both mm and MODx.
- Bug fix: Corrected a dodgy regexp which was causing URL resolution problems
  when installing into a subdirectory.
- Updated the friendly URL config to include standard MODx stuff (avoids
  confusion about whether it should be there or not)
- Updated the root name trailing slash redirection to be consistent with apache
  guidelines.
- stripAlias is now implemented for multilingual URLs. Need to check that it
  works on pre-Evo installs.
- stripAlias can result in empty aliases. Need to handle that.
- Implemented automatic pagetitle update
- Implemented better document pagetitle synchronisation
- Started implementing automatic alias updating.
- Bug fix: YAMS could return HTTP OK for monolingual documents with an extra
  root name prefix. Fixed. Now permanent redirects to correct monolingual URL.
- Implemented mime dependent aliases. Currently not possible to set the
  mime-alias map via the module interface.
- Modified the YAMS to encode tv names in the same way that MODx does for
  0.9.6.3 and earlier versions. (Previously the encoding was not done in
  completely the same way.)
- Altered the PreParse method to prevent the recursion limit from being reached
  on complicated documents. It now returns a flag that says whether it needs to
  be called again.
- Tidied up the comments in the code a bit.
- Bug fix: Corrected a missing variable declaration in yams.module.inc.php

Version 1.1.7 alpha RC1
-  Bug fix: Corrected (I hope) and URL bug which would affect documents nested
   at level 2 and greater when using friendly alias paths.

Version 1.1.6 alpha
- Added SEO Strict style URL functionality. YAMS will now permanent redirect
  to the correct URL when
  * slashes are incorrectly specified (multiple slashes or missing trailing slash)
  * the prefix and suffix of a filename are missed
  * the prefix and suffix are included for a folder
  In addition, there is now a new option that allows the re-writing of containers
  as folders: .../mycontainer.html -> .../mycontainer/
  Currently there is no facility for overriding this on a document by document
  basis.
- Introduced a new redirection mode: "current else browser". When redirecting to
  a multilingual page, if the site has been visited previously and a language
  cookie has been saved, the visitor will be redirected to a page in that language.
  Otherwise they will be redirected to a page based on their browser settings.

Version 1.1.6 alpha RC1
- Bug Fix: Fixed a problem whereby documents could break when loading them from
  the cache.
- Bug Fix: Fixed a server config bug affecting rootname to rootname/ redirection.
- Bug Fix: Repaired a missing space in a mysql query.

Version 1.1.5 alpha
- Bug Fix: Fixed a parse bug which could occasionally involve a regular
  expression grabbing too much and breaking YAMS constructs.

Version 1.1.5 alpha RC3:
- Bug Fix: Corrected a bug in breadcrumbs.101.yams.snippet.php
- Made use of the new ((yams_data:...)) construct to optimise
  breadcrumbs.101.yams.snippet.php by minimising database queries.
- Renamed breadcrumbs.101.yams.snippet.php to give it the php extension.
  Also included code to protect it against direct execution.
- PreParseOptimise is fairly resource intensive, so only call it if
  pre-parse-optimisation is really required (that is, if there is more than one
  nested yams-select construct.)
- yams_doc and yams_docr were really inefficient, because each document alias
  was requiring at least one database query. Now use a cache which stores the alias
  of each document in each language, as well as its parent. This can bring result
  in major performance enhancements
- Updated the server config to include redirection from mysite.com/root to
  mysite.com/root/
- Bug Fix: Made sure yams-in blocks get parsed on the final PostParse call. As a
  result of this fix Ditto will no longer complain that it can't find the language
  file when using &language=`(yams_mname)`
- It is not necessary for PostParse to be called recursively. Fixed.
- YAMS was executing on the OnWebPageComplete event... but this was completely
  unnecessary. Fixed.
- Bug Fix: Fixed misplaced PostParse argument.
- Bug Fix: Corrected a bug which would cause the current language block of a
  repeat construct to be output as blank when no currentTpl was specified. (In
  this case, the repeatTpl should be used.)
- Updated the StoreYamsInCallback and StoreYamsRepeatCallback to use a new
  YamsCounter number rather than using the number of the block being cached.

Version 1.1.5 alpha RC2:
- Bug Fix: Corrected another URL encoding bug that would prevent incorrect
  changing of language and which sometimes gave rise to blank pages.

Version 1.1.5 alpha RC1:
- Bug Fix: Corrected an URL encoding bug that would prevent incorrect changing
  of language.
- Updated the Wayfinder and Ditto extensions to use [[YAMS? &get=`data`.
- Updated the manager manager rules to ensure that the template variables are
  moved to tabs in the correct order. Wasn't sure if the existing array_merge
  was simply concatenating the sorted arrays.
- Updated the YAMS snippet cal to make use of the ((yams_data:... syntax.
  [[YAMS? &get=`content` is now depracated and [[YAMS? &get=`data` should be
  used in its place
- First implementation of the ((yams_data:docId:tvname:phx)) syntax for improved
  performace through minimisation of the number of sql queries. Does not
  support PHx yet. Loads data from a maximum of YAMS_DOC_LIMIT documents at time.
- Bug Fix: Fixed several bugs introduced when updating the parsing. YAMS
  placeholders can now go almost anywhere.
- Fairly major changes to parsing:
  - YAMS now ensures that all chunks and tvs - which may contain YAMS placholders
  - are parsed on the PreParse step before handing over to MODx.
  - It should now be possible to include yams placeholders in cacheable AND
    uncacheable snippet calls and in chunk and tv names...
- Updated documentation to describe multilingual alias modes in more detail.
- Modified to allow the monolingual URL and (one of) the multilingual URLs
  to coincide. Now, when unique multilingual aliases are not being used it
  is only necessary for the multilingual language variants to be unique.
- Bug Fix: Fixed a bug whereby tv's would be incorrectly sorted in the document
  view.
- Included a How TO? for custom multilingual tvs/chunks/snippets.
- Updated the multilingual URL generation and checking so as to exclude deleted
  documents.
- Bug Fix: Corrected a bug whereby YAMS would not change language when on the
  site start document.

Version 1.1.4 alpha:
- Bug Fix: Corrected the Wayfinder How To? module documentation.
- Implemented automatic redirection of weblinks. This wasn't implemented before.
  This works with multilingual weblinks too. In the content fields of a
  multilingual weblink it is possible to specify the same or different URLs or
  docIds for each language. When using a docId, the target document will be
  displayed in the same language as the source document, or the default language
  if the final document is monolingual.
- Made the $yams->ConstructURL( $langId, $docId ) method public so that it can
  be used as a replacement for $modx->makeUrl( $docId ) when YAMSifying existing
  snippets etc.
- Bug Fix: Correct a bug in the implementation of friendly url suffixes and prefixes.
  This bug made the suffixes and prefixes active at every level instead of just
  the filename.
- Bug Fix: Updated the server config. It now displays the correct output when
  unique multilingual aliases only are being used. It also advises on virtual
  host configuration when server name mode is being used.
- Corrected a potential bug whereby the second argument of preg_quote was not
  specified.
- Reorganised the params on the 'Other Params' tab and updated the multilingual
  alias text a bit.


Version 1.1.3 alpha:
- Added support for friendly alias prefixes and friendly alias suffixes.
- Bug Fix: Corrected server config bug. ${...} should have been %{...}
- Added support for phx modifiers on multilingual document variables. The
  following are examples of accepted syntax:
  [*#content:lcase*]
  [*content:limit=`300`*]
- Replaced YAMS' own recursive chunk parsing call an iterative call to
  MODx's mergeChunkContent. Seemed silly not to reuse existing code.
- Now YAMS maintains a list of monolingual document ids, to avoid having to
  look up whether a document is multilingual in the database each time.
- Bug Fix: Fixed a bug in the server config. Was using the output query param
  separator instead of the input one. As a result was getting double encoded
  ampersands.
- Modified the default output query separator (used when it is not defined by
  PHP) to be &amp; rather than &.
- Bug Fix: Fixed problem whereby invalid monolingual URLs which are invalid due
  to a multilingual servername being used would redirected to themselves.
- Bug Fix: Fixed a bug whereby the alias of the site-start document was being
  included in the URL when using friendly alias paths and when it shouldn't have
  been because of a YAMS setting.
- Sort of bug fix: I have removed the mb_strtolower function from the URL
  comparison since mbstring library is not active by default in PHP. Was going
  to replace it by strtolower - which would have been safe on the encoded URL.
  However, since MODx does not support case insensitive URLs anyway - so I have
  removed it. True support for case insensitive URLs would be possible but would
  require a bit more thought.
- Bug Fix: Fixed bug active when friendly alias paths is on which was causing
  docs lower than the root to not be found.

Version 1.1.2 alpha:
- Now possible to view document using index.php?id=... So, preview from the document
  tree now works again.
- Fixed a bug wherby callbacks were being registered statically when they shouldn't
  have been.

Version 1.1.1 alpha:
- Modified the default multilingual URLs generated by YAMS so that the alias
  of the default language variant is the same as that of the document variable.
- Implemented a 'Unique Multilingual Aliases' mode. This mode is activated if
  unique multilingual aliases are being used. In that case it is not
  necessary to specify root names or server names. YAMS can determine the language
  group id and document id directly from the alias. The documentation needs
  updating now.
- Improved commenting of the code a little.
- Applied proper URL encoding to the subdirectory and root name.

Version 1.1.0 alpha:
- Generalised generated query strings to use the php defined argument separator
- Added a parameter for specifying whether aliases are unique or not.
- Updated the copying over of default content for multilingual aliases.
- Now does proper encoding of URLs. Multibyte URLs are correctly encoded.
- Added correct conversion from native encoding to utf-8 to rawurlencoded and
  back again for URLs and query parameters.
- Added methods for escaping and cleaning strings for embedding in (x)html/xml
  documents and updated all occurrences of htmlspecialchars to use them.
- Arranged it so that the current language cookie is only set if a valid document
  is found and it is multilingual.
- If a multilingual alias has not been specified, then nothing is output for the
  URL.
- Incorporated mbowman's YAMS_UX code into YAMS.
  * Generalised it to function with and without friendly alias paths.
  * Generalised it to function with or without multilingual alias mode
  * Generalised it to take into account absent filename for site start (only
    default language if using multilingual aliases).
  * Fixed incorrect langId specification.
- YAMS now manages the alias document variable associated with multilingual
  aliases.
- Default descriptions for Multilingual TVs created by YAMS are now in the correct
  language.
- YAMS now manages a list of supported document variable types.
- Allowed (*.)localhost as a valid server name

Version 1.0.5 beta:
- Fixed bug: Pagetitle document variable not being synchronised with default
  language pagetitle for multilingual documents on MODx > Evo 1.0.0-RC1.
  (Problem had same origin as previous managermanager bug.)
- Fixed bug: In releases of MODx prior to MODx Evolution final 404 not found
  errors would occur when accessing the site root.

Version 1.0.4 beta:
- Optimised the redirection by bringing it forward to OnWebPageInit.
- Added new inactive languages to the default YAMS configuration:
  German and Russian.
- Updated all the documentation based on experience gained with practical
  implementation of YAMS.
- Fixed the YAMS ManagerManager extension so that it works with MODx Evolution
- A new extension is available that allows Jot to function seemlessly with YAMS!
  See the How To? tab.
- Fixed a major bug whereby all pages were returning 404 not founds. Turns out
  this was partly MODx's fault.
- Included an additional parameter that allows the status code for the redirection
  that occurs when responding to a request to change language to be set.
- Included an additional parameter that allows the status code for redirection to
  the default and to other languages to be set separately. This is important for
  migrating a site from monolingual to multilingual.
- Made the IsValidMonolingualRequest and IsValidMultilingualRequest methods public
- Corrected an error wherby a default language block was being required when it
  wasn't actually necessary
- Corrected an error in a check for an invalid language in the Expand method.
- PCRE backtracking parameter was too small compared to the potential size of
  the templates and was giving rise to blank pages for large documents.
  Increased size of parameter to maximum number of characters that can appear in
  the content field (approximately).
- Optimised regular expressions by specifying ungreedy content matching where
  appropriate.
- Optimised regular expressions by specifying opening parts of constructs as once
  only sub-patterns.
- Updated the reg-exp callback arrays to use YAMS in capitals so as to match the
  class name.
- Corrected yams->YAMS typo in documentation
- Altered the format of the language string accepted by the YAMS snippet &from
  parameter. The separators are now double colons :: and double pipes || rather
  than single ones.
  This fixes some regexp problems and brings the syntax closer to that used for
  specifying template variable options. (Sorry for the change, but I thought
  that it was better to fix this sooner rather than later.)
- Updated advice on the use of eForm on the Documentation>How To? tab.
- Updated the advice on the choice of HTTP status code for automatic language
  redirection.
- New feature: MODx internal URLs are now automatically handled by YAMS and
  do not need to be replaced by YAMS placeholders. (No harm done for URLs which
  already been replaced by YAMS placeholders.)
  As a result, it is no longer necessary to update all MODx internal URLs as
  part of the set-up process. YAMS will now automatically handle standard MODx
  style URLs provided they are surrounded by double quotes - which they almost
  always are in (X)HTML. See step 5 of the updated setup guide on the
  Documentation>Setup tab of the YAMS module interface.
- Corrected a bug whereby YAMS would not recognise QuickEdit style template
  variables starting with a # symbol.
- Corrected a bug whereby pagetitles would be synchronised whether or not this
  had been requested.
- Corrected badly encoded Japanese text in the default settings.
- Corrected a bug whereby cached documents weren't being redirected correctly.
- Corrected a bug whereby the YAMS snippet would try to grab multilingual content
  from a monolingual document.
- Corrected a bug in the server settings suggested by MODx when the MODx install
  is located in a server subdirectory
- Fixed several bugs relating to correct output of document URLs when the MODx
  install is located in a subdirectory.
- Corrected a bug whereby a MODx parse error would occur when adding a new
  language while having no multilingual templates defined.
- Corrected a bug whereby settings that had been set but not saved during the
  failed edit or addition of a language group were being displayed in the module
  interface as if they had been saved until the page was reloaded.
- When adding a new language group allow the MODx language to be selected via a
  dropdown.
- Did some reorganising of the files. Only files that users need to refer to
  /include when setting up yams live at the root directory now. All yams
  php classes, including the main yams.class.inc.php file, live in the class
  subdirectory. There are now also css and js subdirectories.
- Replaced a few occurences of strpos and substr by equivalent code using preg
  for UTF-8 compatiblity.
- Removed the text about the OnDocFormSave event not being required when the
  document page title synchronisation option is not selected, since it was
  confusing. Advice is now to always have the plugin active on this event.
- Added an extra configuration parameter on the Other Params tab that allows the
  MODx install to be pointed to a subdirectory.
- Started preparing for cleaned module code and multilingual module interface.

Version 1.0.3 alpha:
- Updated the documentation. Note that the advice is now to always run the YAMS
  snippet as cacheable</strong> to enable YAMS to optimise the snippet output
  before the document is cached.
- Included some more How Tos.
- Modified the server name regexp to allow specification of IP based addresses.
- Updated the YAMS snippet to allow templatable content to be repeated over all
  active languages. The list, select and select form options were updated to make
  them use this templatable approach. These options now use default file-based
  templates that can be overriden by the user.
- YAMS placeholders normally decide what the current language is based on context.
  The placeholder now can be forced to evaluate itself using the document
  language by appending a + symbol to the placeholder name. For example (yams_id+)
  This functionality has also been added to the EasyLingual placeholders for
  completeness.
- Added several new placeholders which enable access to other YAMS parameters.
  (yams_confirm), (yams_change), (yams_name_in_..), (yams_choose)
- Added a yams-in block which forces content to be displayed as if a specified
  language is currently active.
- Added an optional (current) sub-block to the yams-repeat syntax.
- Renamed yams-multi to yams-repeat, since mutli is a bit ambiguous.
- Added an additional mode (query mode) whereby the current language is
  identified via a query parameter rather than different server and root names.
  This enables closer compatibility with EasyLingual.
- Made it possible to change the page language via GET as well as POST
- Made the name of the GET/POST parameter used to change language configurable
  via the module interface
- Added an additional pre-parse step on OnLoadDocument before any MODx parsing
  has taken place and to the OnWebPageComplete event before the document is
  cached. Pre-parsing evaluates all placeholders before snippets are evaluated
  so that YAMS placeholders can be used in snippet calls.
- Brought the page redirection forward to OnParseDocument for efficiency.
- Brought determination of the current language forward to OnWebPageInit
- Added an additional optimisation and evaluation step after the OnLoadDocument,
  OnParseDocument and OnWebPageComplete events. This optimises away yams-select
  blocks, leaving a single block in the version of the document that gets cached.
  This minimises the number of yams-select blocks which need to be parsed during
  the OnWebPagePrerender event and allows for complete compatibility with
  EasyLingual. It also allows YAMS placeholders to be used within snippet calls,
  and these may even be within chunks.
- Allowed the user to associate a MODx language name with a YAMS language group.
  For use with snippets like Ditto and eForm.
- Added a new placeholder, (yams_mname), which provides access to the MODx
  language name associated with the current language. This can be used with
  snippets like ditto and eForm like so:
  [!Ditto? &language=`(yams_nmame)` ... !]
- Also updated the EasyLingual compatibility placeholders [%language%] and
  [%LANGUAGE%] to access the specified MODx language name.
- Fixed incorrect language attribute in language select form.
- Added language direction attributes to the language list and language select
  form.
- Added javascript to the language select drop down to automatically submit
  language changes. No button presses required now.
- Fixed an error in the ManagerManager whereby it wasn't tabifying deactivated
  languages.
- Added guidance on multilingual eForm templates.

Version 1.0.2 alpha:
- Fixed setting of MODx theme and language direction.
- Added the (yams_dir) and (yams_align) placeholders
- Setting of language direction via the YAMS module is now possible
- Added an EasyLingual compatibility mode and placeholders.
- Renamed pagetitle on multilingual documents to "Internal Name"
- Provided an option to synchronise the content of the document pagetitle with
  the default language pagetitle field for multilingual documents.
- * Renamed a couple of the existing YAMS placeholders*
  (yams_url) -> (yams_site)
  (yams_burl) -> (yams_server)
- Added a couple of new placeholders to help with Wayfinder integration and
  linking of documents: (yams_doc) (yams_docr)
- Included an option which affects the new placeholders.
  Allows the omission of the filename of the document in the URL
  if it is the site start document
- Updated the Wayfinder templates.
  * The new templates are in different directories,
  so you will need to update your Wayfinder Snippet calls.
  See the Setup tab for guidance. *
- Updated the documentation, and * installation guidance,
  particularly with respect to linking to other documents. *

Version 1.0.1 alpha:
- Prevented warning about missing config file on first run

Version 1.0 alpha:
- Initial alpha release