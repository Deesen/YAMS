<?php
  $langName = 'English (British)';
  $langDir = 'ltr';
  // $langTimeStampFormat = 'r';
  if ( isset( $tpl ) && $tpl instanceof Templator )
  {
    $tpl->RegisterPlaceholder( 'txt_lang', 'en' );
    $tpl->RegisterPlaceholder( 'txt_direction', 'ltr' );
    $tpl->RegisterPlaceholder( 'txt_module_head_title', 'YAMS Module Configuration' );
    $tpl->RegisterPlaceholder( 'txt_module_title', 'YAMS: Yet Another Multilingual Solution' );
    $tpl->RegisterPlaceholder( 'txt_header_configuration', 'Configuration' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_title', 'Documentation' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_title', 'About' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_short_description', 'A highly configurable multilingual solution that doesn\'t require the user to maintain multiple document trees and which allows the user to work with existing document templates.' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_author_title', 'Author' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_donate_title', 'Donate' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_donate_text', 'If you would like to support the time spent developing, maintaining and supporting YAMS, please <a href="[+yams_donate_en_url+]" target="_blank">donate</a>. If you would like to purchase support on a more formal basis, please <a href="[+yams_contact_en_url+]" target="_blank">contact Nashi Power</a>.' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_version_title', 'Version' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_version_text', '[+yams_version+] for testing. Check for the latest version in the <a href="[+yams_package_url+]" target="_blank">MODx Add-Ons repository</a>.' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_copyright_title', 'Copyright (and example site)' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_licence_title', 'Licence' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_forums_title', 'Forums' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_forums_text', 'A list of planned developments for YAMS is maintained in the <a href="[+yams_forums_url+]" target="_blank">MODx Forums support thread</a>.' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_new_title', 'New in this version' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_new_text'
      , '<ul>
          <li>Removed the text about the OnDocFormSave event not being required when the
            document page title synchronisation option is not selected, since it was
            confusing. Advice is now to always have the plugin active on this event.</li>
          <li>Added an extra configuration parameter on the Other Params tab that
          allows the MODx install to be pointed to a subdirectory.</li>
          <li>Started preparing for cleaned module code and multilingual module interface.</li>
        </ul>'
    );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_description_title', 'Description' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_description_text'
      , '<p>YAMS allows users to define language groups and specify certain templates
        as multilingual. All documents associated with those templates are then
        accessible in multiple languages via different URLs. The different language
        versions can be distinguished by root folder name or by server name.
        Unlike other multilingual solutions, it is NOT necessary to manage multiple
        document trees with YAMS. Instead, all content for all languages is stored
        in template variables in the multilingual documents. YAMS has a ManagerManager
        extension that will organise the template variables for different languages
        onto different tabs. YAMS is also capable of creating and managing the
        multilingual template variables automatically. Whether or not a template
        is multilingual can be configured simply via the module interface.</p>
        <p>Multi-language content for the main document variables (pagetitle,
        longtitle, description, introtext, menutitle, content) is handled automatically
        and transparently and is subject to normal MODx caching. No special syntax
        is required for these document variables. For example, use
        <code>[*content*]</code> and the correct language content will appear.</p>
        <p>In addition, YAMS provides a range of placeholders which provide access
        to the language tag (for use in <code>lang=&quot;&quot;</code> or
        <code>xml:lang=&quot;&quot;</code>), the language direction (for use in
        <code>dir=&quot;&quot;</code>), language name and which allow the insertion
        of multilingual URLs for the current or any other document.  These can be
        used throughout the site, including in snippet templates.</p>
        <p>More advanced functionality is available via the YAMS snippet call.
        For example, via the snippet call it is possible to repeat content over
        multiple languages using templates. It is also possible to generate language
        lists or drop-down boxes in order to change language.</p>
        <p>Since snippets are generally responsible for parsing the placeholders
        in templates supplied to them, like <code>[&#43;pagetitle&#43;]</code> for
        example, they wont automatically know to insert the correct multilingual
        content. For Ditto I have overcome this by writing an extender which fixes
        this issue. For the templates of other snippets it is possible to overcome
        this problem by replacing these placeholders by special YAMS snippet calls, eg:
        <br />
        <code>[[YAMS? &amp;get=`content` &amp;from=`pagetitle` &amp;docid=`[&#43;id&#43;]`]]</code><br />
        I have provided YAMS compatible default templates for Wayfinder which
        already include the appropriate YAMS snippet calls.</p>
        <p>As of version 1.0.3, YAMS *should be* fully compatible with
        <a href="http://modxcms.com/forums/index.php/topic,32807.0.html" target="_blank">EasyLingual</a>.
        See the Setup tab for instructions on how to migrate a site from EasyLingual to YAMS.</p>
        <p>YAMS has been developed on MODx Evolution v0.9.6.3 and with the latest
        version of PHP (5.2.6-3). I haven\'t made any effort as yet to make it
        backwards compatible with older versions of either.</p>'
      );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_about_credits_title', 'Credits' );
    $tpl->RegisterPlaceholder(
      'txt_tab_documentation_about_credits_text'
      , 'The language icons used in the language select drop down are from
        <a href="http://www.languageicon.org/" target="_blank">Language Icon</a>,
        released under the
        <a href="http://creativecommons.org/licenses/by-sa/3.0/">Attribution-Share Alike
        3.0 Unported</a> license. Thanks to @MadeMyDay for having the courage to
        be one of the first guinea pigs!'
    );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_title', 'Setup' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_intro'
      , '<p>These instructions aim to tell you how to set up a new site or convert
      an existing site to be multilingual in a way that will cause you the least
      disruption. In theory it\'s possible to convert your site to a multilingual
      one without having to take it off line for more than a second while you
      reload your server config. If everything goes smoothly, then at no point
      should your website be broken during the setup process.</p>
      <p>There are two sets of instructions. The first is for those people who
      are starting from scratch and want to develop a multilingual site, or those
      or have a monolingual site and would like to add additional languages. The
      second is for those people who are already using the EasyLingual solution
      and would like to convert to using YAMS so as to benefit from caching and
      all the other benefits that YAMS brings.</p>' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_title', 'Starting from scratch or from a mono-lingual site' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_intro'
      , '<p>It will be assumed that you have already installed the YAMS module,
      plugin and snippet. The DocFinder module can come in really useful for
      updating your URLs, but it is not obligatory. I highly recommend installing
      the ManagerManager plugin, since YAMS can use it to organise the multiple
      language fields into separate tabs and to hide redundant document variable
      fields on the document view.</p>
      <p>YAMS has been designed to work with friendly URLs. It <em>should</em>
      work with friendly alias paths on, but <strong>this has not yet been
      thoroughly tested</strong>.</p>
      <p>The default YAMS install does nothing. You have to set it up to switch
      on the multilingual functionality that you need.</p>' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_caption', 'Step by Step YAMS Setup');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_summary', '[+txt_tab_documentation_setup_fromscratch_table_caption+]');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_step', 'Step');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_action', 'Action');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_description', 'Description');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_backup_action', 'Ensure you\'re backed up');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_backup_description', '<p>This is an alpha version after all.</p>');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_urlformat_action', 'Decide on URL format');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_urlformat_description'
    , '<p>Before you start configuring you need to decide how
        you will identify the different languages groups in the URL.
        There are three modes that can be used: <strong>Server Name</strong> mode,
        <strong>Root Name</strong> mode and <strong>Query Param</strong> mode.
        Whatever combination of modes you choose, the full URL
        must be unique for each language and you will need to configure your server settings appropriately.</p>
        <h3>Server Name and Root Name modes</h3>
        <p><strong>Server Name</strong> mode and <strong>Root Name</strong> mode can be used simultaneously or independently.
        The general format of the URL when using these modes is:<br />
        <code>http://(server_name)/(root_name)/(path/)(filename)</code><br />
        where there is only a path if friendly alias paths have been configured.</p>

      <p>When server name mode is OFF the server name is determined
        in the normal way MODx determines it and so is consistent with MODx [(site_url)].
        Server name mode is switched ON by specifying a different server name for each
        language group and a server name for monolingual/ordinary pages on the Language Settings tab.
        To use server name mode, you will have to configure the
        various server names as aliases or virtual hosts on your server.</p>

      <p>To enable root name mode, you just need to specify at least
        one root name on the Language Settings tab. If server name mode
        is OFF then you will need to specify one per language group.</p>

      <p>In either of these modes it is possible to change the language of a page
      by sending a request back to current page with the id of the new
      language group specified with a GET or POST variable. By default this variable is
      called yams_new_lang, but it can be configured on the Other Params tab. There is
      also a placeholder that can be used to access this name and snippet calls that
      will generate a list or drop down box to enable changing the language.
     </p>

      <h3>Query Param mode</h3>
      <p><strong>Query Param</strong> mode is provided primarily for compatibility
        with EasyLingual and cannot be used in conjunction with the other modes.
        In this mode there is no root name and the server name is identical for all
        language groups. So, to distinguish one language from another a query parameter
        is appended to all URLs:<br />
        <code>http://server_name/(path/)filename?yams_lang=id</code></p>
      <p>
        By default this query param is called yams_lang.
        However, it is configurable on the Other Options tab.
        Change it to <code>lang</code> for compatibility with EasyLingual.</p>');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_langsettings_action', 'Configure Language Settings');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_langsettings_description'
    , '<p>The second step is to configure the language settings for
          each language group that will be used on your multilingual pages. This
          is done on the language settings tab.</p>

        <p>Each language group has an id. This is used, for example, in the
        multilingual versions of the template variables for that language.
        Eg: <code>description_<em>id</em></code></p>

        <p>A language group can be set up to represent a group of languages (en),
        a specific localised language (en-gb) or a selection of localised languages
        (en-gb,en-us,...) by specifying a comma separated list of language tags.</p>

        <p>In addition to this and the server name and root name for each language
        group, you can specify the language direction and the text associated with
        each language. You can also associate a MODx language name with the language
        group.</p>

        <p>You will now need to set up one language group as the default language
        group. This language will be assumed for non-multilingual pages.</p>' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_friendlyurls_action', 'Update Friendly URLs config');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_friendlyurls_description'
      , '<p>If you are using server name or root name mode then
          you will need update your .htaccess file to strip the
          root directory name from the beginning of the request
          URI (if necessary) and send MODx the correct <code>q</code>
          query parameter. To help with this, YAMS provides you with
          text that you can copy and paste. See the Server Config tab.
          As a bonus, this text will tell YAMS the current language
          via a query parameter, which saves it from having to
          figure it out from the URL later.</p>

        <p>You will have to update your server config every time you
          activate or deactivate a language group, change its
          server or root name, or rename the query parameter.</p>

        <p>Note that your website should continue to function normally.</p>' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_updateurls_action', 'Update URLs');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_updateurls_description'
      , '<p>The next step is to update all URLs to internal documents and weblinks
        so that they will function as multilingual URLs on multilingual pages and
        normal URLs otherwise. Links to real files like images are not affected
        by the YAMS module and do not need to be converted. To convert an URL
        you use the YAMS placeholders.</p>
        <p>The recommended way of specifying URLs in documents is<br />
          <code>(yams_doc:<em>docId</em>)</code> or <code>(yams_docr:<em>docId</em>)</code><br />
          and in templates is<br />
          <code>(yams_doc:[&#43;<em>docId</em>&#43;])</code> or <code>(yams_docr:[&#43;<em>docId</em>&#43;])</code>.
        </p>
        <p>This will provide a full absolute URL to the given document. The
        <code>yams_docr</code> version will additionally resolve weblinks if that
        is required. These two placeholders can be configured not to output the
        filename of the document for the site start. See the \'Other Params\' tab.
        This approach will work for all YAMS setups and is not dependent on a
        particular base meta tag setting.</p>
        <p>However, if you are ONLY using root name mode and are not planning on
        using server name mode in the future then you may find it simpler to prefix
        the page alias by the root name instead. For example:</p>
        <ul>
          <li><code>[~<em>docId</em>~]</code> becomes <code>(yams_root/:<em>docId</em>)[~<em>docId</em>~]</code></li>
          <li><code>[~[&#43;<em>docId</em>&#43;]~]</code> becomes <code>(yams_root/:[&#43;<em>docId</em>&#43;])[~[&#43;<em>docId</em>&#43;]~]</code></li>
          <li><code>[(site_url)][~<em>docId</em>~]</code> becomes <code>[(site_url)](yams_root/:<em>docId</em>)[~<em>docId</em>~]</code></li>
          <li><code>[(base_url)][~<em>docId</em>~]</code> becomes <code>[(base_url)](yams_root/:<em>docId</em>)[~<em>docId</em>~]</code></li>
          <li>etc.</li>
        </ul>
        <p>You could of course choose to include the root name in the base meta
        tag (by setting the href attribute to <code>(yams_site)</code> for example.)
        However, please be aware that in that case you will have to provide a
        complete URL to all file based resources. For example:
        <code>[(site_url)]assets/images/...</code></p>
        <p>Remember that by default all pages are monolingual, so none of these
        replacements should break or alter the way your website functions.</p>');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_updatetags_action', 'Update Language Tags and Direction');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_updatetags_description', '<p>The next step is to update your language and language direction attributes, again using YAMS placeholders: <code>lang=&quot;(yams_tag)&quot;</code> and/or <code>xml:lang=&quot;(yams_tag)&quot;</code> and <code>dir=&quot;(yams_dir)&quot;</code></p>');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_updatesnippets_action', 'Update Snippets');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_updatesnippets_description'
      , '<p>The next step is to update any snippets which output URLs
          or directly contain multilingual text that is not embedded in multilingual
          placeholders.
          Guidance on how to do this for Wayfinder, Ditto, eForm and
          other snippets is on the \'How To?\' tab.</p>
        <p>Note that you can use the <code>(yams_mname)</code> placeholder
          to access the correct manager language for use in snippet calls. For
          example, with ditto and eForm,
          <code>&amp;language=`(yams_mname)`</code> can be used.</p>');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_redirection_action', 'Redirection Strategy');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_redirection_description', '<p>You are now at a stage where you can start to specify certain templates as multilingual templates. All documents associated with those templates will have multiple language versions - but they will all be managed via the same document within the manager. Note that when you specify a template as multilingual, the URLs of the associated documents will change. YAMS will automatically redirect from the old URLs to a language variant. Several redirection modes are available. Initially you should choose the default language mode. Only once you have written content for the other languages should you switch. These settings are configured on the Other Params tab. It is recommended to set the HTTP status code for the redirect to temporary until you are confident you want to go live.</p>');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_managermanager_action', 'ManagerManager Interface');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_managermanager_description'
      , '<p>If you have not installed ManagerManager yet and are planning to, then
        now is the time to do it. It is highly recommended. Once you have done it,
        insert the following line into your mm_Rules configuration file to enable
        YAMS to integrate with it:<br />
          <code>require( $modx->config[\'base_path\'] . \'assets/modules/yams/yams.mm_rules.inc.php\' );</code></p>
        <p>You will subsequently be able to specify how the fields in your multilingual
        documents will be organised via the settings on the Other Params tab.</p>
        <p>When you convert a document to be a multilingual document the existing
        document variables, including the pagetitle, retain their existing values.
        However, all but the pagetitle become redundant. The YAMS ManagerManager
        rules will hide the redundant document variables. With YAMS, the document
        pagetitle takes on the role of a text identifier for the document and all
        its language variants within the MODx back-end. This identifier is
        visible in the MODx document tree, but not on any web output. For
        convenience, YAMS provides an option to automatically update this document
        pagetitle with the contents of the default language multilingual pagetitle
        template variable on document save. This can be enabled via the option
        on the \'Other Params\' tab.</p>');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_multilingualtpl_action', 'Multilingual Templates');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_multilingualtpl_description'
      , '<p>You are finally in a position to be able to specify certain templates
        as multilingual. You do this from the Multilingual Templates tab. I recommend
        leaving the automatic template variable management setting to yes. Note that
        this is a beta version though, so make sure you are all backed up
        before hand.</p>
        <p>All you have to do is select yes for those templates that you want to
        be multilingual. If you want to experiment first, create a new template,
        associate it with a new document, populate it with some default content
        and play with that. Assuming you selected yes for automatic template
        variable management, making it multilingual will create multiple versions
        of each template variable for each language, associate it with the template
        and copy over the default content for your document into the newly created
        default language template variables.</p>');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_translate_action', 'Translate');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_translate_description', '<p>You can now view multiple language versions of your documents by browsing to the appropriate URL. However, initially all but the default language version has content written for it. Now is the time to go to each document and translate the content. Note that your site will continue to look normal and there wont be any links pointing to your new language versions until you jump to the next step.</p>');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_publicise_action', 'Publicise');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_publicise_description'
      , '<p>Once your content is translated, you are in a position to publicise it.
      You can use the snippet calls<br />
      <code>[[yams? &amp;get=`list`]]</code><br />
      or<br />
      <code>[[yams? &amp;get=`selectform`]]</code><br />
      to include a list based or form based language selection tool into your template.
      These commands can now be modified using custom templates.
      See the snippet documentation for more details.</p>');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_done_action', 'All Done');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_fromscratch_table_heading_row_done_description'
      , '<p>Your site is now up and running as a multilingual site.
        You can change the redirection mode if you like and can
        change the http status once you are happy that you are
        going to keep your site as a multilingual site. Make sure
        your search engine site map contains a list of all documents,
        and not just those of a single language.
        See the How To tab for more details about how to achieve this.</p>');
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_easylingual_title', 'Migrating from an EasyLingual site' );
    $tpl->RegisterPlaceholder( 'txt_tab_documentation_setup_easylingual_text'
      , '<p><strong>Warning!</strong> I have never actually tried to convert an EasyLingual site before. I have just read about how it is supposed to work from the EasyLingual forum thread. Looking for feedback from volounteers willing to try it out.</p>
        <ul>
          <li>First back up. (See step 1 above.)</li>
          <li>On the Other Params tab select the EasyLingual Compatibility Mode setting to Yes
          and change the Confirm Language Param setting to <code>lang</code>.</li>
          <li>You will have to use Query Param mode. (See step 2 above for details.)</li>
          <li>Configure your languages using the Language Settings tab. (See step 3 above.)
            Leave all server name fields blank. (The default site url will be displayed
            in brackets.) Leave the root names fields blank.</li>
        </ul>
        <p>Your site <em>should</em> now be working as it was under EasyLingual.
        If it is not, then take a look at the EasyLingual tab for info about
        the EasyLingual compatibility placeholders. If that still does not solve
        your problem them please post a message to the YAMS forum thread. Some
        additional adjustments may be needed or there may be a bug.</p>' );
    
  }
  
?>