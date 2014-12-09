=== Unyson ===
Contributors: unyson, themefusecom
Tags: page builder, cms, grid, layout, responsive, back up, backup, db backup, dump, migrate, schedule, search engine optimization, seo, media, slideshow, shortcode, slide, slideshare, slideshow, google sitemaps, sitemaps, analytics, google analytics, calendar, event, events, google maps, learning, lessons, sidebars, breadcrumbs, review, portfolio
Requires at least: 4.0.0
Tested up to: 4.0.1
Stable tag: 2.1.4
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

For extending or theming Unyson, see [developers documetation](http://unyson-docs.themefuse.com/en/latest/).

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