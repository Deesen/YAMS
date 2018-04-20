<?php
  $langName = 'Français';
  $langDir = 'ltr';
  // $langTimeStampFormat = 'r';
  if ( isset( $tpl ) && $tpl instanceof Templator )
  {
    $tpl->RegisterPlaceholder( 'txt_lang', 'fr' );
    $tpl->RegisterPlaceholder( 'txt_direction', 'ltr' );
    $tpl->RegisterPlaceholder( 'txt_module_head_title', 'Configuration de Module de YAMS' );
    $tpl->RegisterPlaceholder( 'txt_module_title', 'YAMS: Encore une Solution Multilingue' );
    $tpl->RegisterPlaceholder( 'txt_header_configuration', 'Configuration' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_title', 'Documentation' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_title', 'A propos' );
    // $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_short_description', 'A highly configurable multilingual solution that doesn\'t require the user to maintain multiple document trees and which allows the user to work with existing document templates.' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_author_title', 'Auteur' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_donate_title', 'Faire un don' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_version_title', 'Version' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_version_text', '[+yams_version+] pour tester. Trouver la version la plus recente dans <a href="[+yams_package_url+]" target="_blank">le MODx Add-Ons repository</a>.' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_copyright_title', 'Droits d\'auteur (et un site d\'example)' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_licence_title', 'Licence' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_forums_title', 'Forums' );
    // $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_forums_text', 'A list of planned developments for YAMS is maintained in the <a href="[+yams_forums_url+]" target="_blank">MODx Forums support thread</a>.' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_new_title', 'Nouveau dans cette version' );
//    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_new_text'
//      , '<ul>
//                    <li>Removed the text about the OnDocFormSave event not being required when the
//  document page title synchronisation option is not selected, since it was
//  confusing. Advice is now to always have the plugin active on this event.</li>
//                    <li>Added an extra configuration parameter on the Other Params tab that allows the MODx install to be pointed to a subdirectory.</li>
//                    <li>Started preparing for cleaned module code and multilingual module interface.</li>
//                  </ul>'
//    );
//    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_description_title', 'Description' );
//    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_description_text'
//      , '<p>YAMS allows users to define language groups and specify certain templates
//        as multilingual. All documents associated with those templates are then
//        accessible in multiple languages via different URLs. The different language
//        versions can be distinguished by root folder name or by server name.
//        Unlike other multilingual solutions, it is NOT necessary to manage multiple
//        document trees with YAMS. Instead, all content for all languages is stored
//        in template variables in the multilingual documents. YAMS has a ManagerManager
//        extension that will organise the template variables for different languages
//        onto different tabs. YAMS is also capable of creating and managing the
//        multilingual template variables automatically. Whether or not a template
//        is multilingual can be configured simply via the module interface.</p>
//        <p>Multi-language content for the main document variables (pagetitle,
//        longtitle, description, introtext, menutitle, content) is handled automatically
//        and transparently and is subject to normal MODx caching. No special syntax
//        is required for these document variables. For example, use
//        <code>[*content*]</code> and the correct language content will appear.</p>
//        <p>In addition, YAMS provides a range of placeholders which provide access
//        to the language tag (for use in <code>lang=&quot;&quot;</code> or
//        <code>xml:lang=&quot;&quot;</code>), the language direction (for use in
//        <code>dir=&quot;&quot;</code>), language name and which allow the insertion
//        of multilingual URLs for the current or any other document.  These can be
//        used throughout the site, including in snippet templates.</p>
//        <p>More advanced functionality is available via the YAMS snippet call.
//        For example, via the snippet call it is possible to repeat content over
//        multiple languages using templates. It is also possible to generate language
//        lists or drop-down boxes in order to change language.</p>
//        <p>Since snippets are generally responsible for parsing the placeholders
//        in templates supplied to them, like <code>&#91;+pagetitle+&#93;</code> for
//        example, they wont automatically know to insert the correct multilingual
//        content. For Ditto I have overcome this by writing an extender which fixes
//        this issue. For the templates of other snippets it is possible to overcome
//        this problem by replacing these placeholders by special YAMS snippet calls, eg:
//        <br />
//        <code>[[YAMS? &amp;get=`content` &amp;from=`pagetitle` &amp;docid=`&#91;+id+&#93;`]]</code><br />
//        I have provided YAMS compatible default templates for Wayfinder which
//        already include the appropriate YAMS snippet calls.</p>
//        <p>As of version 1.0.3, YAMS *should be* fully compatible with
//        <a href="http://modxcms.com/forums/index.php/topic,32807.0.html" target="_blank">EasyLingual</a>.
//        See the Setup tab for instructions on how to migrate a site from EasyLingual to YAMS.</p>
//        <p>YAMS has been developed on MODx Evolution v0.9.6.3 and with the latest
//        version of PHP (5.2.6-3). I haven\'t made any effort as yet to make it
//        backwards compatible with older versions of either.</p>'
//      );
//    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_credits_title', 'Credits' );
//    $tpl->RegisterPlaceholder(
//      'txt_tab_documentation_about_credits_text'
//      , 'The language icons used in the language select drop down are from
//        <a href="http://www.languageicon.org/" target="_blank">Language Icon</a>,
//        released under the
//        <a href="http://creativecommons.org/licenses/by-sa/3.0/">Attribution-Share Alike
//        3.0 Unported</a> license. Thanks to @MadeMyDay for having the courage to
//        be one of the first guinea pigs!'
//    );

  }
  
?>