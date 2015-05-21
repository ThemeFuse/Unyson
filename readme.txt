=== Unyson ===
Contributors: unyson, themefusecom
Tags: page builder, cms, grid, layout, responsive, back up, backup, db backup, dump, migrate, schedule, search engine optimization, seo, media, slideshow, shortcode, slide, slideshare, slideshow, google sitemaps, sitemaps, analytics, google analytics, calendar, event, events, google maps, learning, lessons, sidebars, breadcrumbs, review, portfolio, framework
Requires at least: 4.0.0
Tested up to: 4.2
Stable tag: 2.3.1
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

= 2.3.1 =
* Fixed [#566](https://github.com/ThemeFuse/Unyson/issues/566), [#550](https://github.com/ThemeFuse/Unyson/issues/550)
* Fixed: Options default values not working in Customizer [#410](https://github.com/ThemeFuse/Unyson/issues/410#issuecomment-103343955)

= 2.3.0 =
* Options can be used in [Customizer](https://codex.wordpress.org/Theme_Customization_API) [#410](https://github.com/ThemeFuse/Unyson/issues/410)
* Fixed [#77](https://github.com/ThemeFuse/Unyson/issues/77)

= 2.2.10 =
* Fixed [#539](https://github.com/ThemeFuse/Unyson/issues/539)

= 2.2.9 =
* Fixed [#530](https://github.com/ThemeFuse/Unyson/issues/530), [#529](https://github.com/ThemeFuse/Unyson/issues/529), [#502](https://github.com/ThemeFuse/Unyson/issues/502)
* Fixes for [#520](https://github.com/ThemeFuse/Unyson/issues/520)
* Minor fix in autosave

= 2.2.8 =
* Fixed [#453](https://github.com/ThemeFuse/Unyson/issues/453)
* Improved option type `multi-picker` html render [#442](https://github.com/ThemeFuse/Unyson/issues/442)
* Option type `rgba-color-picker` optimizations [#442](https://github.com/ThemeFuse/Unyson/issues/442)
* `fw_resize()` improvements [#447](https://github.com/ThemeFuse/Unyson/issues/447)
* Fixed [#445](https://github.com/ThemeFuse/Unyson/issues/445), [#161](https://github.com/ThemeFuse/Unyson/issues/161), [#484](https://github.com/ThemeFuse/Unyson/issues/484), [#456](https://github.com/ThemeFuse/Unyson/issues/456)
* Added the possibility to prevent box auto-close [#466](https://github.com/ThemeFuse/Unyson/issues/466)
* Fixed the `_get_value_from_input()` method in some option types [#275](https://github.com/ThemeFuse/Unyson/issues/275#issuecomment-94084590)
* Added the `limit` parameter for option type `addable-popup` [#478](https://github.com/ThemeFuse/Unyson/issues/478)
* Fixed popup position in IE [#483](https://github.com/ThemeFuse/Unyson/issues/483)
* Created `fw_post_options_update` action
* Improved post save: Options are saved in revision and autosave. Restore from revision works.

= 2.2.7 =
* Option type `popup` fixes
* Added "Show/Hide other extensions" button [#307](https://github.com/ThemeFuse/Unyson/issues/307)
* `fw.soleModal` added `afterOpen` and `afterClose` callbacks [#379](https://github.com/ThemeFuse/Unyson/issues/379)
* Fixed [#432](https://github.com/ThemeFuse/Unyson/issues/432), [#408](https://github.com/ThemeFuse/Unyson/issues/408)

= 2.2.6 =
* Fixed [#404](https://github.com/ThemeFuse/Unyson/issues/404)
* Added the Translation extension in Available Extensions list

= 2.2.5 =
* Fixed [PageBuilder#26](https://github.com/ThemeFuse/Unyson-PageBuilder-Extension/issues/26)

= 2.2.4 =
* Fixed [#398](https://github.com/ThemeFuse/Unyson/issues/398)
* Removed option type `runnable` [#399](https://github.com/ThemeFuse/Unyson/issues/399)

= 2.2.3 =
* Fixed [#397](https://github.com/ThemeFuse/Unyson/issues/397), [#394](https://github.com/ThemeFuse/Unyson/issues/394), [#389](https://github.com/ThemeFuse/Unyson/issues/389), [#384](https://github.com/ThemeFuse/Unyson/issues/384), [#355](https://github.com/ThemeFuse/Unyson/issues/355)
* Added option type `runnable`

= 2.2.2 =
* Added experimental `$option['option_handler']` [636ed56](https://github.com/ThemeFuse/Unyson/commit/636ed56fe499a4e855b5f49198747460833539a3)
* `<input required ... />` works in `fw.OptionsModal` [#274](https://github.com/ThemeFuse/Unyson/issues/274)
* Fixed [#381](https://github.com/ThemeFuse/Unyson/issues/381), [#382](https://github.com/ThemeFuse/Unyson/issues/382), [#385](https://github.com/ThemeFuse/Unyson/issues/385), [Shortcodes#15](https://github.com/ThemeFuse/Unyson-Shortcodes-Extension/issues/15)

= 2.2.1 =
* Fixed: Sub-extensions were not loaded [#368](https://github.com/ThemeFuse/Unyson/issues/368)
* Fixed: $extension->locate_URI('/...') bug

= 2.2.0 =
* Added the possibility to load extensions from any directory

    ```
    function _filter_my_plugin_extensions($locations) {
        $locations['/path/to/plugin/extensions'] = 'https://uri.to/plugin/extensions';
        return $locations;
    }
    add_filter('fw_extensions_locations', '_filter_my_plugin_extensions');
    ```

    **Important!** Prefix your extension names to prevent conflicts.

* Removed `array_merge($old_opts, $new_opts)` from options save [#266](https://github.com/ThemeFuse/Unyson/issues/266)
* Tabs, Boxes, Groups, Options are now displayed in the order they are in array (not grouped) [#319](https://github.com/ThemeFuse/Unyson/issues/319)
* Option type `multi-picker` fixes [#296](https://github.com/ThemeFuse/Unyson/issues/296)
* Added the possibility to use custom `framework-customizations` directory name [#276](https://github.com/ThemeFuse/Unyson/issues/276)
* Minor fixes

= 2.1.25 =
* Fixed qTranslate function name
* Improved default flash messages display position in frontend
* Option-type icon: Minor css changes
* Minor fix on extensions install: The success state was set too early

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
