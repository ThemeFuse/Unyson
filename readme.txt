=== Unyson ===
Contributors: unyson
Tags: page builder, cms, grid, layout, responsive, back up, backup, db backup, dump, migrate, schedule, search engine optimization, seo, media, slideshow, shortcode, slide, slideshare, slideshow, google sitemaps, sitemaps, analytics, google analytics, calendar, event, events, google maps, learning, lessons, sidebars, breadcrumbs, review, portfolio, framework
Requires at least: 4.0.0
Tested up to: 4.4
Stable tag: 2.5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple and easy way to build a powerful website.  

== Description ==

[Unyson](http://unyson.io/) - A free drag & drop framework that comes with a bunch of built in extensions that will help you develop premium themes fast & easy.

[vimeo https://vimeo.com/113008662]

**Features include:**

* **Drag & Drop Page Builder.** Create countless pages using the content and media shortcodes.
* **Sliders.** To make you life even easier we have already built in 3 of them that support images and videos.
* **Mega Menu.** User-friendly drop down menu that will let you easily create highly customized menu configurations.
* **Sidebars.** This module will let your users customize WordPress pages with dynamic sidebars.
* **Portfolio.** Portfolio has some pretty neat filtering animations.
* **Breadcrumbs.** A pretty small module that will really come in handy when you’ll want to navigate your website faster.
* **Forms** Use the drag & drop form builder to create any contact form you'll ever want or need.
* **SEO.** SEO settings at finger tips without installing further plugins.
* **Feedback.** We've added a way for users to submit reviews and ratings for events, projects, etc.
* **Events.** It's pretty simple to use and it has Calendar and Map shortcodes.
* **Backup & Demo Content.** Create an automated backup schedule, import demo content or even create a demo content archive for migration purposes.

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

You can open issues via Unyson Github Repository [Issues page](https://github.com/ThemeFuse/Unyson/issues).

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

= 2.5.0 =
* Added the possibility to save options in separate database location [#838](https://github.com/ThemeFuse/Unyson/issues/838)

	Will be used in next release of the PageBuilder extension, the builder value will be moved to a separate post meta.

* Lazy Tabs: Render only the visible tabs [#1174](https://github.com/ThemeFuse/Unyson/issues/1174)

	This feature can be disabled by adding in `{theme}/framework-customizations/theme/config.php`:

		$cfg['lazy_tabs'] = false;

* Added the [`.pot` file](https://github.com/ThemeFuse/Unyson/tree/master/framework/languages) [#1256](https://github.com/ThemeFuse/Unyson/issues/1256)

* Fixed [#1072](https://github.com/ThemeFuse/Unyson/issues/1072), [#1052](https://github.com/ThemeFuse/Unyson/issues/1052), [#1235](https://github.com/ThemeFuse/Unyson/issues/1235), [#1236](https://github.com/ThemeFuse/Unyson/issues/1236), [#1251](https://github.com/ThemeFuse/Unyson/issues/1251), [#1246](https://github.com/ThemeFuse/Unyson/issues/1246), [#1242](https://github.com/ThemeFuse/Unyson/issues/1242), [#941](https://github.com/ThemeFuse/Unyson/issues/941), [#1250](https://github.com/ThemeFuse/Unyson/issues/1250), [#1243](https://github.com/ThemeFuse/Unyson/issues/1243), [#1261](https://github.com/ThemeFuse/Unyson/issues/1261)

= 2.4.17 =
* Fixed [#1197](https://github.com/ThemeFuse/Unyson/issues/1197)
* Added `'oembed'` option type ([docs](http://manual.unyson.io/en/latest/options/built-in-option-types.html#oembed))
* fixed: `fw_get_db_customizer_option()` infinite recursion when called with default value inside `customizer.php`

= 2.4.16 =
* Fixed [#1178](https://github.com/ThemeFuse/Unyson/issues/1178), [#1179](https://github.com/ThemeFuse/Unyson/issues/1179), [#1169](https://github.com/ThemeFuse/Unyson/issues/1169), [#1085](https://github.com/ThemeFuse/Unyson/issues/1085)

= 2.4.15 =
* Fixed [#1164](https://github.com/ThemeFuse/Unyson/issues/1164), [#1080](https://github.com/ThemeFuse/Unyson/issues/1080), [#1006](https://github.com/ThemeFuse/Unyson/issues/1006), [#1139](https://github.com/ThemeFuse/Unyson/pull/1139), [#1152](https://github.com/ThemeFuse/Unyson/pull/1152), [#1157](https://github.com/ThemeFuse/Unyson/pull/1157)

= 2.4.14 =
* Customizer options: Added `wp-customizer-args` parameter. [#1097](https://github.com/ThemeFuse/Unyson/issues/1097), [#1082](https://github.com/ThemeFuse/Unyson/issues/1082)

        $options = array(
            'b' => array(
                'wp-customizer-args' => array( // <---
                    'priority' => 10,
                    'active_callback' => 'is_front_page',
                ),
                'type' => 'box',
                'options' => array(
                    'o' => array('type' => 'text')
                )
            )
        );

* Option type `switch`: Changed html input value structure. [#1083](https://github.com/ThemeFuse/Unyson/issues/1083)
* Improved the `FW_WP_Filesystem` helper. [#1127](https://github.com/ThemeFuse/Unyson/issues/1127)

= 2.4.13 =
* **IMPORTANT!!!** Updating from v2.4.12 to any newer version will uninstall all extensions [#1105](https://github.com/ThemeFuse/Unyson/issues/1105#issuecomment-163966468)

    We are very sorry

    To install all extensions compatible with your theme, open the following link:

    ```
    {site.domain}/wp-admin/admin.php?page=fw-extensions&sub-page=install&supported
    ```

* Fixed [#1089](https://github.com/ThemeFuse/Unyson/issues/1089), [#1077](https://github.com/ThemeFuse/Unyson/pull/1077#issuecomment-163324948)

= 2.4.12 =
* WordPress 4.4 fixes
* Fixed
  [#1039](https://github.com/ThemeFuse/Unyson/issues/1039),
  [#1044](https://github.com/ThemeFuse/Unyson/issues/1044),
  [#1055](https://github.com/ThemeFuse/Unyson/pull/1055),
  [#1072](https://github.com/ThemeFuse/Unyson/issues/1072),
  [#1049](https://github.com/ThemeFuse/Unyson/issues/1049),
  [#1086](https://github.com/ThemeFuse/Unyson/issues/1086),
  [PageBuilder#40](https://github.com/ThemeFuse/Unyson-PageBuilder-Extension/issues/40#issuecomment-160135453)

= 2.4.11 =
* Fixed a bug in `popup` option-type: Options were not saved

= 2.4.10 =
* New extension: [Backup & Demo Content](http://manual.unyson.io/en/latest/extension/backups/#content) *(Note: It will not be visible in extensions list if old Backup extension is set as supported in [theme manifest](http://manual.unyson.io/en/latest/manifest/theme.html#content))*
* Soft removed 3 extensions: Styling, Translation, Learning [#874](https://github.com/ThemeFuse/Unyson/issues/874)
* Added option type `short-slider` (same as `slider`, but it's smaller)
* Fixed html validation error. Added current url in `FW_Form` `<form action="...">`
* Option type `upload`: Added `files_ext` and `extra_mime_types` parameters ([docs](http://manual.unyson.io/en/latest/options/built-in-option-types.html#upload))
* `fw.Modal`: added `headerElements` attribute to set html elements in modal header
* `fw.soleModal`: added `backdrop: null|bool` parameter to set backdrop transparent, white or dark
* Added classes: `FW_Type` and `FW_Type_Register` *(used in the new Backup extension)*
* Fixed [#993](https://github.com/ThemeFuse/Unyson/issues/993)
* RTL fixes
* Other minor fixes ([the diff](https://github.com/ThemeFuse/Unyson/compare/v2.4.9...v2.4.10))

= 2.4.9 =
* Fixed [#889](https://github.com/ThemeFuse/Unyson/issues/889) Some scripts/styles were enqueued too early
* Minor fixes and improvements ([diff](https://github.com/ThemeFuse/Unyson/compare/v2.4.8...v2.4.9))

= 2.4.8 =
* Option type `color-picker` and `rgba-color-picker`: Added "Reset" button [#831](https://github.com/ThemeFuse/Unyson/issues/831)
* Fixed [#853](https://github.com/ThemeFuse/Unyson/issues/853), [#856](https://github.com/ThemeFuse/Unyson/issues/856), [#794](https://github.com/ThemeFuse/Unyson/issues/794), [#865](https://github.com/ThemeFuse/Unyson/issues/865), [#873](https://github.com/ThemeFuse/Unyson/issues/873), [#876](https://github.com/ThemeFuse/Unyson/issues/876)
* Unycon v2: Removed strange (not useful) icons

    Demo and search `http://{your-site.com}/wp-content/plugins/unyson/framework/static/libs/unycon/`

* For extension developers: Now you can set font icon as [thumbnail](http://manual.unyson.io/en/latest/manifest/extension.html)

    ```
    $manifest['thumbnail'] = 'fa fa-btc'; // or 'dashicons' or 'unycon'
    ```

* Extensions list: Changed extension buttons *(Install, Activate, Deactive, Remove)* position to make more space for description

= 2.4.7 =
* FontAwesome 4.4.0
* Improved extension ajax install/uninstall [#809](https://github.com/ThemeFuse/Unyson/issues/809)
* Fixed [#829](https://github.com/ThemeFuse/Unyson/issues/829), [#820](https://github.com/ThemeFuse/Unyson/issues/820), [Shortcodes#31](https://github.com/ThemeFuse/Unyson-Shortcodes-Extension/issues/31)

= 2.4.6 =
* Fixed: Extensions uninstalled after auto-update [#263](https://github.com/ThemeFuse/Unyson/issues/263)

= 2.4.5 =
* Fixed [#768](https://github.com/ThemeFuse/Unyson/issues/768)
* Create `ABSPATH .'/fw-update.log'` on plugin update to help solve [#263](https://github.com/ThemeFuse/Unyson/issues/263)

= 2.4.4 =
* Fixed [#757](https://github.com/ThemeFuse/Unyson/issues/757), [#752](https://github.com/ThemeFuse/Unyson/issues/752#issuecomment-124839194)
* fw.OptionsModal: Prevent Reset button to change the value. It should reset only the form's html
* Minor refactor in code that is responsible for auto-save post options save

= 2.4.3 =
* [An attempt](https://github.com/ThemeFuse/Unyson/commit/9985875d56520caae4ce72c7111aea1e326777a5) to fix [#263](https://github.com/ThemeFuse/Unyson/issues/263)

= 2.4.2 =
* Allow containers without the `type` parameter in [Customizer options](http://manual.unyson.io/en/latest/options/introduction.html#customizer)

= 2.4.1 =
* Fixed [#742](https://github.com/ThemeFuse/Unyson/issues/742), [#731](https://github.com/ThemeFuse/Unyson/issues/731), [#728](https://github.com/ThemeFuse/Unyson/issues/728), [#726](https://github.com/ThemeFuse/Unyson/issues/726), [Shortcodes#29](https://github.com/ThemeFuse/Unyson-Shortcodes-Extension/issues/29)
* Fixed: `addable-popup` option type wasn't saved in Customizer

= 2.4.0 =
* Created "Container Types" and container type `popup` [#615](https://github.com/ThemeFuse/Unyson/issues/615)
* Created the `fw_collect_options()` function [#740](https://github.com/ThemeFuse/Unyson/issues/740)
* Added Unycon font icon. Demo `http://{your-host}/wp-content/plugins/unyson/framework/static/libs/unycon/demo/`
* Minor fixes

= 2.3.3 =
* Fixed [#628](https://github.com/ThemeFuse/Unyson/issues/628), [#649](https://github.com/ThemeFuse/Unyson/issues/649), [#637](https://github.com/ThemeFuse/Unyson/issues/637), [#358](https://github.com/ThemeFuse/Unyson/issues/358)
* Added option type `typography-v2`
* Option type `addable-option`, `addable-box` and `addable-popup`: Added the `add-button-text` and `sortable` parameters [#631](https://github.com/ThemeFuse/Unyson/issues/631)
* Optimized option type `typography` fonts select (now it loads faster)
* Extension download improvement: Fetch only [the latest release](https://developer.github.com/v3/repos/releases/#get-the-latest-release) instead of [all releases](https://developer.github.com/v3/repos/releases/#list-releases-for-a-repository)
* Improved the `fw.loading` js helper. Changed the loading image.
* Option type `range-slider`: fixed bug with default value
* Option type `checkbox` and `switch`: Fix boolean values when the option is used in options modal inside other options
* Call `FW_Option_Type::_init()` after the option type has been registered
* `fw.OptionsModal`: Added html cache [#](https://github.com/ThemeFuse/Unyson/issues/675#issuecomment-112555557)
* Added `$old_value` parameter in `'fw_post_options_update'` action
* Added the `fw_get_image_sizes()` function
* Enabled the `colorpicker` and `textcolor` plugins for the `wp-editor` option type
* Option type `background-image` and `multi-picker`: minor css fixes
* Updated `selectize.js` to `0.12.1`

= 2.3.2 =
* Added option-type `unique` to make possible [this](http://manual.unyson.io/en/latest/extension/shortcodes/index.html#enqueue-shortcode-dynamic-css-in-page-head)
* Added the `fw_get_google_fonts_v2()` function
* Fixed: The newline character in textarea option in Customizer was replaced with `'rn'` string
* Fixed: Post options (meta) lost after Quick Edit
* Option-type `multi-picker`: Allow support for any option type in picker ([docs](http://manual.unyson.io/en/latest/options/built-in-option-types.html#multi-picker-add-support-for-new-option-type-in-picker))

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

