=== Unyson ===
Contributors: unyson
Tags: page builder, grid, layout, responsive, back up, backup, db backup, dump, migrate, schedule, search engine optimization, seo, media, slideshow, shortcode, slide, slideshare, slideshow, google sitemaps, sitemaps, analytics, google analytics, calendar, event, events, google maps, learning, lessons, sidebars, breadcrumbs, review, portfolio, framework
Requires at least: 4.4
Tested up to: 4.6
Stable tag: 2.6.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple and easy way to build a powerful website.  

== Description ==

[Unyson](http://unyson.io/) - A free drag & drop framework that comes with a bunch of built in extensions that will help you develop premium themes fast & easy.

[Sponsored by: BitBlox.me](http://bitblox.me/)

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

* WordPress 4.4 or greater
* PHP version 5.2.4 or greater
* MySQL version 5.6 or greater

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

= 2.6.8 =
* Extensions are installed on plugin install with TGM `'is_automatic' => true` [#2117](https://github.com/ThemeFuse/Unyson/issues/2117)
* Fixed [#2134](https://github.com/ThemeFuse/Unyson/pull/2134), [#2104](https://github.com/ThemeFuse/Unyson/issues/2104), [#2106](https://github.com/ThemeFuse/Unyson/issues/2106), [#1144](https://github.com/ThemeFuse/Unyson/issues/1144#issuecomment-250257511)

= 2.6.7 =
* Fixed [#2053](https://github.com/ThemeFuse/Unyson/issues/2053), [#2024](https://github.com/ThemeFuse/Unyson/issues/2024)

= 2.6.6 =
* Disabled File Cache [#2029](https://github.com/ThemeFuse/Unyson/issues/2029)
* Added option-type `addable-popup-full` [#1769](https://github.com/ThemeFuse/Unyson/issues/1769#issuecomment-247054955)
* Fixed [#2034](https://github.com/ThemeFuse/Unyson/issues/2034), [#2025](https://github.com/ThemeFuse/Unyson/issues/2025), [#2031](https://github.com/ThemeFuse/Unyson/issues/2031)

= 2.6.5 =
* [Minor fixes](https://github.com/ThemeFuse/Unyson/compare/v2.6.4...v2.6.5)

= 2.6.4 =
* Fixed [#2000](https://github.com/ThemeFuse/Unyson/issues/2000)

= 2.6.3 =
* Fixed [#1993](https://github.com/ThemeFuse/Unyson/issues/1993)

= 2.6.2 =
* Option-types must be loaded on `fw_option_types_init` [action](http://manual.unyson.io/en/latest/options/create-option-type.html?#create-option-type) [#1827](https://github.com/ThemeFuse/Unyson/issues/1827)
* Option-type `icon-v2` refactor
* Fixed [#1491](https://github.com/ThemeFuse/Unyson/issues/1491), [#1979](https://github.com/ThemeFuse/Unyson/issues/1979), [#1970](https://github.com/ThemeFuse/Unyson/issues/1970), [#1954](https://github.com/ThemeFuse/Unyson/issues/1954)

= 2.6.1 =
* Hotfix in File Cache [#1968](https://github.com/ThemeFuse/Unyson/issues/1968)

= 2.6.0 =
* Added [File Cache](https://github.com/ThemeFuse/Unyson/blob/16709330f1acc29453928fce0fafe69a8ea592c7/framework/helpers/class-fw-file-cache.php) [#1828](https://github.com/ThemeFuse/Unyson/issues/1828)
* Migration to native term meta [#1745](https://github.com/ThemeFuse/Unyson/issues/1745)
* The possibility to [register Available Extensions from theme](https://github.com/ThemeFuse/Unyson/blob/16709330f1acc29453928fce0fafe69a8ea592c7/framework/core/components/extensions/manager/class--fw-extensions-manager.php#L232-L238)
* Fixed [#1860](https://github.com/ThemeFuse/Unyson/issues/1860), [#1877](https://github.com/ThemeFuse/Unyson/issues/1877), [#1897](https://github.com/ThemeFuse/Unyson/issues/1897), [#1810](https://github.com/ThemeFuse/Unyson/issues/1810)

= 2.5.12 =
* Fixed `wp-editor` option error when used in Theme Settings [#1860](https://github.com/ThemeFuse/Unyson/issues/1860)

= 2.5.11 =
* New extension: WordPress Shortcodes [#1807](https://github.com/ThemeFuse/Unyson/issues/1807)
* Option type `wp-editor` fixes [#1615](https://github.com/ThemeFuse/Unyson/issues/1615)
* Performance improvement in `fw_get_db_..._option()` functions
* Added javascript helper `fw.soleConfirm` [#1803](https://github.com/ThemeFuse/Unyson/pull/1803)

= 2.5.10 =
* Fixed `fw_get_db_customizer_option()` bug [#1796](https://github.com/ThemeFuse/Unyson/issues/1796)

= 2.5.9 =
* Fixed missing function in WP < 4.5 [#1767](https://github.com/ThemeFuse/Unyson/issues/1767)
* New option-type: [icon-v2](http://manual.unyson.io/en/latest/options/built-in-option-types.html#icon-v2)
* Fixed `wp-editor` option-type bugs [#1739](https://github.com/ThemeFuse/Unyson/issues/1739)
* Process `fw-storage` parameter in all options *(Theme Settings, Customizer, Post, Term, Extension Settings)* [#1551](https://github.com/ThemeFuse/Unyson/issues/1551)

= 2.5.8 =
* Fixed `wp-editor` bugs
* Updated FontAwesome
* Fixed [#1676](https://github.com/ThemeFuse/Unyson/issues/1676)

= 2.5.7 =
* [#1682](https://github.com/ThemeFuse/Unyson/pull/1682), [#1687](https://github.com/ThemeFuse/Unyson/pull/1687), [#1647](https://github.com/ThemeFuse/Unyson/pull/1647)

= 2.5.6 =
* [Minor fixes](https://github.com/ThemeFuse/Unyson/compare/v2.5.5...v2.5.6#files_bucket)

= 2.5.5 =
* Fixed [#1586](https://github.com/ThemeFuse/Unyson/issues/1586)

= 2.5.4 =
* Fixed [#1423](https://github.com/ThemeFuse/Unyson/issues/1423), [#1517](https://github.com/ThemeFuse/Unyson/issues/1517), [#1509](https://github.com/ThemeFuse/Unyson/issues/1509), [#1386](https://github.com/ThemeFuse/Unyson/issues/1386), [#1488](https://github.com/ThemeFuse/Unyson/issues/1488), [#837](https://github.com/ThemeFuse/Unyson/issues/837), [#1538](https://github.com/ThemeFuse/Unyson/issues/1538), [#1484](https://github.com/ThemeFuse/Unyson/issues/1484)

= 2.5.3 =
* Fixed option-type `wp-editor` issues: [#1472](https://github.com/ThemeFuse/Unyson/issues/1472), [#1475](https://github.com/ThemeFuse/Unyson/issues/1475), [#1478](https://github.com/ThemeFuse/Unyson/issues/1478)
* Improvements in `fw_get_db_post_option()`
* Updated FontAwesome to v4.6.1

= 2.5.2 =
* Fixed option-type `wp-editor` issues [#15](https://github.com/ThemeFuse/Unyson-Shortcodes-Extension/issues/15#issuecomment-207368593)
* Taxonomy options now are displayed on Add Term page [#1427](https://github.com/ThemeFuse/Unyson/pull/1427)
* Added the `wp-customizer-setting-args` parameter for customizer options [#1435](https://github.com/ThemeFuse/Unyson/issues/1435)
* Added translations: [Nederlands](https://www.transifex.com/themefuse/dashboard/all_resources/nl_NL/?project=unyson), [Русский](https://www.transifex.com/themefuse/dashboard/all_resources/ru_RU/?project=unyson)
* Updated [Español](https://www.transifex.com/themefuse/dashboard/all_resources/es_ES/?project=unyson) translations
* Fixed [#1449](https://github.com/ThemeFuse/Unyson/issues/1449), [#1438](https://github.com/ThemeFuse/Unyson/issues/1438), [#1278](https://github.com/ThemeFuse/Unyson/issues/1278#issuecomment-208032542), [#1443](https://github.com/ThemeFuse/Unyson/issues/1443), [#1440](https://github.com/ThemeFuse/Unyson/issues/1440)

= 2.5.1 =
* Fixed [#1062](https://github.com/ThemeFuse/Unyson/issues/1062), [#1278](https://github.com/ThemeFuse/Unyson/issues/1278), [#1292](https://github.com/ThemeFuse/Unyson/issues/1292), [#1293](https://github.com/ThemeFuse/Unyson/issues/1293), [#1310](https://github.com/ThemeFuse/Unyson/pull/1310), [#1295](https://github.com/ThemeFuse/Unyson/issues/1295), [#767](https://github.com/ThemeFuse/Unyson/issues/767), [#1322](https://github.com/ThemeFuse/Unyson/pull/1322), [#1323](https://github.com/ThemeFuse/Unyson/pull/1323), [#1321](https://github.com/ThemeFuse/Unyson/issues/1321), [#1054](https://github.com/ThemeFuse/Unyson/issues/1054), [#1309](https://github.com/ThemeFuse/Unyson/issues/1309), [#1347](https://github.com/ThemeFuse/Unyson/issues/1347), [#2777093](https://wordpress.org/support/topic/bootstrap-datepicker-not-translated-on-backend), [#1355](https://github.com/ThemeFuse/Unyson/issues/1355), [#1354](https://github.com/ThemeFuse/Unyson/issues/1354), [#1379](https://github.com/ThemeFuse/Unyson/issues/1379), [#1394](https://github.com/ThemeFuse/Unyson/issues/1394), [#1391](https://github.com/ThemeFuse/Unyson/issues/1391), [#1403](https://github.com/ThemeFuse/Unyson/pull/1403)
* Fixes for [WP 4.5 BackboneJS & UnderscoreJS latest version](https://make.wordpress.org/core/2016/02/17/backbone-and-underscore-updated-to-latest-versions/)


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


