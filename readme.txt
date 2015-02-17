=== Unyson ===
Contributors: unyson, themefusecom
Tags: page builder, cms, grid, layout, responsive, back up, backup, db backup, dump, migrate, schedule, search engine optimization, seo, media, slideshow, shortcode, slide, slideshare, slideshow, google sitemaps, sitemaps, analytics, google analytics, calendar, event, events, google maps, learning, lessons, sidebars, breadcrumbs, review, portfolio, framework
Requires at least: 4.0.0
Tested up to: 4.1
Stable tag: 2.1.24
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple and easy way to build a powerful website.  

== Description ==

[Unyson](http://unyson.themefuse.com/) - A free drag & drop framework that comes with a bunch of built in extensions that will help you develop premium themes fast & easy.

[vimeo https://vimeo.com/113008662]

**Features include:**

* **Drag & Drop Page Builder.** Create countless pages using the content and media shortcodes.
* **Sidebars.** This module will let your users customize WordPress pages with dynamic sidebars.
* **Backup.** Your users will be able to create backups directly from the WordPress admin.
* **Sliders.** To make you life even easier we have already built in 3 of them that support images and videos.
* **SEO.** SEO settings at finger tips without installing further plugins.
* **Breadcrumbs.** A pretty small module that will really come in handy when you’ll want to navigate your website faster.
* **Events.** It's pretty simple to use and it has Calendar and Map shortcodes.  
* **Portfolio.** Portfolio has some pretty neat filtering animations.
* **Feedback.** We've added a way for users to submit reviews and ratings for events, projects, etc.
* **Learning.** Create courses, lessons and quizzes for your online training site.

To get started, check out the [Scratch Theme](https://github.com/ThemeFuse/Scratch-Theme).

**Get involved**

Developers can contribute to the source code on the [Unyson GitHub Repository](https://github.com/ThemeFuse/Unyson/blob/master/CONTRIBUTING.md).

Translators can contribute new languages to Unyson through [Transifex](https://www.transifex.com/projects/p/unyson/).

Theme developers can test the compatibility of their themes with new extensions updates before they are going to be released on [Unyson Extensions Approval](https://github.com/ThemeFuse/Unyson-Extensions-Approval).


== Installation ==

= Minimum Requirements =

* WordPress 4.0 or greater
* PHP version 5.2.4 or greater
* MySQL version 5.0 or greater

= Installation =

1. Upload the `unyson` folder to the `/wp-content/plugins/` directory
1. Activate the Unyson plugin through the 'Plugins' menu in WordPress
1. Configure the plugin by going to the `Unyson` menu that appears in your admin menu


== Frequently Asked Questions ==

= Where can I find Unyson documentation? =

For extending or theming Unyson, see [developers documetation](http://manual.unyson.io/).

= Where can I report bugs or contribute to the project? =

You can report the issue via Unyson Github Repository [Issues page](https://github.com/ThemeFuse/Unyson/issues).

= Where can I request new features? =

The Unyson development team actively participates on [Trello Unyson Development Board](https://trello.com/b/Xm9TxasH/unyson-development), and uses it to communicate with the outside world about its development priorities and tendencies.

= Will Unyson work with my theme? =

Yes; Unyson will work with any theme.


== Screenshots ==

1. Create a page by adding the columns structure you want.
2. Then add content shortcodes to your liking from the Content Elements and Media Elements tabs.
3. All the portfolio entries have thumbs for easy identification.
4. Unyson comes with a built in Events Extension.
5. Create backups directly from the WordPress admin.
6. Adding a new slider will let you select the Type, Population Method.
7. SEO settings at their finger tips without installing further plugins.


== Changelog ==

= 2.1.24 =
* Fixed access denied on Theme Settings and Unyson pages when qTranslate is active
* Made all boxes open when Theme Settings Side Tabs is active (with default tabs only first box is open)
* Minor fixes

= 2.1.23 =
* Improved modal sizes *(Set max-width,max-height pixels instead of top,right,bottom,left percents)*
* Added side tabs styles for Theme Settings page.

    To enable, add in `{theme}/framework-customizations/theme/config.php`

    ```
    $cfg['settings_form_side_tabs'] = true;
    ```

= 2.1.22 =
* Added javascript helper `fw.soleModal()`
* Added `framework/static/js/fw-form-helpers.js`
* Minor fixes
* Theme Settings form ajax submit [#198](https://github.com/ThemeFuse/Unyson/issues/198)

    To disable, add in `{theme}/framework-customizations/theme/config.php`

    ```
    $cfg['settings_form_ajax_submit'] = false;
    ```

= 2.1.21 =
* Made the `forms` and `mailer` extensions hidden.

= 2.1.20 =
* Added warning on Theme Settings reset [^](http://static.md/0fcf01628eddab75fdbedb3a24784db3.png)
* `FW_Form`: Form attributes can be changed in the render method
* Option type `slider` and `range-slider` fixes [#210](https://github.com/ThemeFuse/Unyson/issues/210)
* Option type `typography`: Added filter on standard fonts [#212](https://github.com/ThemeFuse/Unyson/issues/212)
* Option type `radio` and `checkboxes`: Added `inline` parameter [#216](https://github.com/ThemeFuse/Unyson/issues/216)
* Minor fixes

= 2.1.19 =
* Option type `icon`: Updated Font Awesome to 4.3.0
* Option type `typography` fixes [#195](https://github.com/ThemeFuse/Unyson/issues/195)
* Improved hidden standalone extensions auto activation [#203](https://github.com/ThemeFuse/Unyson/issues/203)
* Fixed nested array detection in options array [#204](https://github.com/ThemeFuse/Unyson/issues/204)
* Do not save the options when the "Reset" button was pressed on the Theme Settings page [#197](https://github.com/ThemeFuse/Unyson/issues/197)

= 2.1.18 =
* Added the `FW_WP_List_Table` class
* Option type `multi-picker`: added support for `short-select`
* Option type `slider` and `range-slider` design fixes
* Extension activation fix: Some required extensions were not added for activation
* Fixed wrong `$data['value']` in `FW_Option_Type::_render()` when form validation fails [#188](https://github.com/ThemeFuse/Unyson/issues/188)
* Increase timeout on extensions install [#183](https://github.com/ThemeFuse/Unyson/issues/183)

= 2.1.17 =
* Added the possibility to create a link to an extension settings page `fw()->extensions->manager->get_extension_link('{extension-name}')`

= 2.1.16 =
* Added the "Reset" button on the Theme Settings page
* Minor fixes

= 2.1.15 =
* Minor fix for extension download link

= 2.1.14 =
* Fixed extension download link to not make a request to Github API

= 2.1.13 =
* Make requests to custom Github API Cache service to prevent `Github API rate limit exceeded` error [#138](https://github.com/ThemeFuse/Unyson/issues/138)

= 2.1.12 =
* New extensions: Forms (Contact Forms), Mailer, Social
* Added option type `rgba-color-picker`
* Split the `slider` option-type into `slider` and `range-slider`
* Internal fixes and improvements

= 2.1.11 =
* Added option-type `slider`

= 2.1.10 =
* Activate theme extensions automatically on theme switch and Unyson plugin activation.
* Cache Github API responses for one hour on extensions download to prevent the `API rate limit exceeded` error.

= 2.1.9 =
* Fixed: Extension is not installing if directory already exists but is empty.

= 2.1.8 =
* Minor fixes [#117](https://github.com/ThemeFuse/Unyson/issues/117)

= 2.1.7 =
* Fixed real_path&lt;-&gt;wp_filesystem_path conversion on installations with custom plugins directory (Bedrock WordPress Stack).

= 2.1.6 =
* Fixed the "Cannot create temporary directory" error that happened on some servers where the user have no permissions to access files outside the abspath or home directory.

= 2.1.5 =
* Added `intval()` to all `wp_remote_retrieve_response_code()`. On some servers this function returns a string instead of int and the `$code === 200` verification fails.

= 2.1.4 =
* Improvements for the `fw_google_fonts` filter used to expand the list of available Google fonts. [#100](https://github.com/ThemeFuse/Unyson/issues/100)

= 2.1.3 =
* Multi-site: Only network administrator can install/remove/update extensions

= 2.1.2 =
* Minor fixes and improvements in the extensions installation process

= 2.1.1 =
* Added the `FW_Extension::(get|set)_db_(settings_option|data)()` methods
* Added README.md for Github

= 2.1.0 =
* Moved major theme functionality from `framework-customizations/theme/` to https://github.com/ThemeFuse/Theme-Includes , because the theme must work when the plugin is not installed.
* Removed deprecated usage of the `FW_Option_Type::_render()` for enqueue scripts and style, use `FW_Option_Type::_enqueue_static()` for that

= 2.0.2 =
* Removed the `base64` functions from the `addable-option`, `addable-box` and `addable-popup` option types
* Replaced `file_get_contents()` with `include()` in `helpers/general.php`
* Minor css and js fixes
* Added the `plugin-check-info.md` file

= 2.0.1 =
* Bug Fix: On some servers the path contains a trailing slash http://bit.ly/123amVu . Make sure to remove it.
* Bug Fix: On extension install, required extensions that are already installed were not added for activation.

= 2.0.0 =
* First release.


== Upgrade Notice ==

= 2.0.0 =
* 2.0 is a major update. Unyson Framework as a plugin is working in a different way than build in theme version.
